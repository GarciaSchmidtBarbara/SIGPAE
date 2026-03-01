@extends('layouts.base')

@section('encabezado', 'Subir Documentación')

@section('contenido')

@if(session('error_formato'))
    <div x-data="{ open: true }">
        <x-ui.modal-error
            message="No es posible subir este documento. Verifique formato y tamaño permitido. (PDF, DOC, DOCX, XLS, XLSX, JPG, PNG — máx. 10 MB)" />
    </div>
@endif

@if(session('error_subida'))
    <div x-data="{ open: true }">
        <x-ui.modal-error :message="session('error_subida')" />
    </div>
@endif

@if ($errors->any())
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4 mx-6 mt-4">
        <ul class="list-disc pl-5 text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="p-6 max-w-2xl"
     x-data="{
        contexto: '{{ old('contexto', 'institucional') }}',
        disponibilidad: '{{ old('disponible_presencial', 'presencial') }}',
        archivoNombre: '',
        archivoTamanio: '',
        archivoError: false,
        MAX_SIZE: {{ \App\Models\Documento::MAX_SIZE_BYTES }},
        FORMATOS_OK: ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png'],

        get necesitaEntidad() {
            return this.contexto !== 'institucional';
        },

        onArchivoChange(event) {
            const file = event.target.files[0];
            if (!file) return;
            const ext = file.name.split('.').pop().toLowerCase();
            if (!this.FORMATOS_OK.includes(ext) || file.size > this.MAX_SIZE) {
                this.archivoError = true;
                this.archivoNombre = '';
                this.archivoTamanio = '';
                event.target.value = '';
                return;
            }
            this.archivoError = false;
            this.archivoNombre = file.name;
            this.archivoTamanio = file.size >= 1048576
                ? (file.size / 1048576).toFixed(2) + ' MB'
                : Math.round(file.size / 1024) + ' KB';
        },

        guardar() { this.$refs.formDocumento.submit(); },
     }">

    <form id="form-documento"
          x-ref="formDocumento"
          method="POST"
          action="{{ route('documentos.guardar') }}"
          enctype="multipart/form-data">
        @csrf

        {{-- ── Nombre ─────────────────────────────────────────────── --}}
        <div class="mb-5">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Nombre del documento <span class="text-red-500">*</span>
            </label>
            <input type="text"
                   name="nombre"
                   value="{{ old('nombre') }}"
                   required
                   maxlength="255"
                   class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                   placeholder="Ej: Autorización familiar - Juan Pérez">
        </div>

        {{-- ── Contexto ────────────────────────────────────────────── --}}
        <fieldset class="mb-5 border border-indigo-300 rounded-lg p-4">
            <legend class="text-sm font-semibold text-indigo-700 px-2">Contexto</legend>
            <div class="flex flex-wrap gap-6">
                @foreach([
                    'perfil_alumno' => 'Perfil de alumno',
                    'plan_accion'   => 'Plan de acción',
                    'intervencion'  => 'Intervención',
                    'institucional' => 'Institucional',
                ] as $val => $label)
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="radio"
                               name="contexto"
                               value="{{ $val }}"
                               x-model="contexto"
                               class="accent-indigo-600">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        {{-- ── Entidad asociada (Alumno / Plan / Intervención) ─────── --}}
        <div x-show="necesitaEntidad" x-cloak
             x-transition
             class="mb-5 border border-indigo-300 rounded-lg p-4">

            <p class="text-sm font-semibold text-indigo-700 mb-2">
                <span x-show="contexto === 'perfil_alumno'">Alumno</span>
                <span x-show="contexto === 'plan_accion'">Plan de acción</span>
                <span x-show="contexto === 'intervencion'">Intervención</span>
            </p>

            {{-- Selector Alumno --}}
            <select name="fk_id_entidad"
                    x-show="contexto === 'perfil_alumno'"
                    :disabled="contexto !== 'perfil_alumno'"
                    class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                <option value="">— Seleccione un alumno —</option>
                @foreach($alumnos as $alumno)
                    <option value="{{ $alumno->id_alumno }}"
                        {{ old('fk_id_entidad') == $alumno->id_alumno && old('contexto') === 'perfil_alumno' ? 'selected' : '' }}>
                        {{ $alumno->persona->apellido }}, {{ $alumno->persona->nombre }}
                    </option>
                @endforeach
            </select>

            {{-- Selector Plan de Acción --}}
            <select name="fk_id_entidad"
                    x-show="contexto === 'plan_accion'"
                    :disabled="contexto !== 'plan_accion'"
                    class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                <option value="">— Seleccione un plan —</option>
                @foreach($planes as $plan)
                    <option value="{{ $plan->id_plan_de_accion }}"
                        {{ old('fk_id_entidad') == $plan->id_plan_de_accion && old('contexto') === 'plan_accion' ? 'selected' : '' }}>
                        {{ $plan->descripcion }}
                    </option>
                @endforeach
            </select>

            {{-- Selector Intervención --}}
            <select name="fk_id_entidad"
                    x-show="contexto === 'intervencion'"
                    :disabled="contexto !== 'intervencion'"
                    class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                <option value="">— Seleccione una intervención —</option>
                @foreach($intervenciones as $intervencion)
                    <option value="{{ $intervencion->id_intervencion }}"
                        {{ old('fk_id_entidad') == $intervencion->id_intervencion && old('contexto') === 'intervencion' ? 'selected' : '' }}>
                        N°{{ $intervencion->id_intervencion }}
                        — {{ $intervencion->tipo_intervencion }}
                        ({{ $intervencion->fecha_hora_intervencion?->format('d/m/Y') }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ── Disponibilidad ──────────────────────────────────────── --}}
        <fieldset class="mb-5 border border-indigo-300 rounded-lg p-4">
            <legend class="text-sm font-semibold text-indigo-700 px-2">Disponibilidad del Documento</legend>
            <div class="flex gap-8">
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="radio"
                           name="disponible_presencial"
                           value="presencial"
                           x-model="disponibilidad"
                           class="accent-indigo-600">
                    Presencial
                </label>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="radio"
                           name="disponible_presencial"
                           value="solo_digital"
                           x-model="disponibilidad"
                           class="accent-indigo-600">
                    Solo digital
                </label>
            </div>
        </fieldset>

        {{-- ── Insertar documento ──────────────────────────────────── --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Insertar Documento <span class="text-red-500">*</span>
            </label>

            <div class="flex items-center gap-3">
                <label class="btn-aceptar cursor-pointer">
                    Examinar
                    <input type="file"
                           name="archivo"
                           required
                           class="hidden"
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                           @change="onArchivoChange($event)">
                </label>
                <span class="text-sm text-gray-500" x-text="archivoNombre || 'Ningún archivo seleccionado'"></span>
                <span class="text-xs text-gray-400" x-text="archivoTamanio"></span>
            </div>

            {{-- Error de formato / tamaño --}}
            <p x-show="archivoError"
               class="mt-1 text-xs text-red-600">
                Formato o tamaño no válido. Use: PDF, DOC, DOCX, XLS, XLSX, JPG o PNG (máx. 10 MB).
            </p>

            <p class="mt-1 text-xs text-gray-400">
                Solo archivos en formato PDF, DOC, DOCX, XLS, XLSX, JPG, PNG · Tamaño máximo: 10 MB
            </p>
        </div>

        {{-- ── Botones ─────────────────────────────────────────────── --}}
        <div class="fila-botones">
            <button type="button"
                    class="btn-aceptar"
                    :disabled="archivoError"
                    @click="guardar()">
                Guardar
            </button>
            <a class="btn-volver" href="{{ route('documentos.principal') }}">Volver</a>
        </div>

    </form>



</div>

@endsection
