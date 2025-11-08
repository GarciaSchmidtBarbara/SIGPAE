@extends('layouts.base')

@section('encabezado', 'Crear Familiar')
<!-- FALTA ACOMODAR BIEN EL GRID-->
@section('contenido')
<div x-data="familiarForm()">
    <form method="POST" action="{{ route('familiares.storeAndReturn') }}" @submit="beforeSubmit">
        @csrf

        <p class="separador">Relación</p>
        <div class="flex flex-wrap items-center gap-4 mt-2">
            @php($parentescos = ['padre'=>'Padre','madre'=>'Madre','tutor'=>'Tutor','hermano'=>'Hermano','otro'=>'Otro'])
            @foreach($parentescos as $valor => $label)
                <label class="flex items-center gap-2">
                    <input type="radio" name="parentesco" value="{{ $valor }}" x-model="parentesco" class="text-indigo-600 focus:ring-indigo-500">
                    <span>{{ $label }}</span>
                </label>
            @endforeach
            <input x-show="parentesco==='otro'" x-transition name="otro_parentesco" value="{{ old('otro_parentesco') }}" placeholder="Especificar" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <p class="separador mt-6">Información Personal del Familiar</p>

        <!--Base (o sea, si es padre, madre, tutor u otro)-->
        <div x-show="parentesco!=='hermano'" class="space-y-4 mt-3">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-700 mb-1">DNI</label>
                    <input name="documento" value="{{ old('documento') }}" placeholder="dni familiar" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input name="nombre" value="{{ old('nombre') }}" placeholder="nombre_familiar" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-700 mb-1">Apellido</label>
                    <input name="apellido" value="{{ old('apellido') }}" placeholder="apellido_familiar" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-700 mb-1">Teléfono personal</label>
                    <input name="telefono_personal" value="{{ old('telefono_personal') }}" placeholder="221-123456" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-700 mb-1">Domicilio</label>
                    <input name="domicilio" value="{{ old('domicilio') }}" placeholder="domicilio" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-700 mb-1">Lugar de trabajo</label>
                    <input name="lugar_de_trabajo" value="{{ old('lugar_de_trabajo') }}" placeholder="nombre_trabajo" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-700 mb-1">Fec.Nacimiento</label>
                    <input name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" type="date" placeholder="dd/mm/aaaa" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-700 mb-1">Teléfono laboral</label>
                    <input name="telefono_laboral" value="{{ old('telefono_laboral') }}" placeholder="221-123456" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-700 mb-1">Edad</label>
                    <input name="edad" value="{{ old('edad') }}" placeholder="edad" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-700 mb-1">Nacionalidad</label>
                    <input name="nacionalidad" value="{{ old('nacionalidad') }}" placeholder="Argentina" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <!--Si se marca en el Radio button al hermano-->
        <div x-show="parentesco==='hermano'" class="space-y-4 mt-3">
            <div class="flex items-end gap-3">
                <div class="flex-1">
                    <label class="text-sm font-medium text-gray-700 mb-1">Buscar Alumnos</label>
                    <div class="relative">
                        <input type="text" x-model.debounce.400ms="searchQuery" @input="search()" placeholder="DNI / Nombre / Apellido" class="w-full border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div x-show="results.length" class="absolute z-10 mt-1 w-full bg-white border rounded shadow">
                            <template x-for="al in results" :key="al.id_alumno">
                                <button type="button" @click="selectAlumno(al)" class="w-full text-left px-3 py-2 hover:bg-gray-100">
                                    <span x-text="al.persona.apellido + ', ' + al.persona.nombre"></span>
                                    <span class="text-xs text-gray-500" x-text="' - DNI ' + al.persona.dni"></span>
                                </button>
                            </template>
                            <div x-show="results.length===0" class="px-3 py-2 text-sm text-gray-500">Sin resultados</div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-aceptar" @click="search()">Buscar</button>
            </div>

            <input type="hidden" name="fk_id_persona" :value="selected?.persona?.id_persona || ''">
            <input type="hidden" name="asiste_a_institucion" :value="selected ? 1 : 0">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input name="nombre" :value="field('nombre')" :disabled="isFilled" placeholder="nombre_hermano" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-700 mb-1">Apellido</label>
                    <input name="apellido" :value="field('apellido')" :disabled="isFilled" placeholder="apellido_hermano" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-700 mb-1">Fec. Nacimiento</label>
                    <input name="fecha_nacimiento" :value="field('fecha_nacimiento')" :disabled="isFilled" type="date" placeholder="dd/mm/aaaa" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-700 mb-1">Edad</label>
                    <input name="edad" :value="field('edad')" :disabled="isFilled" placeholder="edad" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-700 mb-1">Domicilio</label>
                    <input name="domicilio" :value="field('domicilio')" :disabled="isFilled" placeholder="domicilio" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-700 mb-1">DNI</label>
                    <input name="documento" :value="field('dni')" :disabled="isFilled" placeholder="dni" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-700 mb-1">Nacionalidad</label>
                    <input name="nacionalidad" :value="field('nacionalidad')" :disabled="isFilled" placeholder="nacionalidad" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="flex items-center gap-2 col-span-2">
                    <input id="asiste" name="asiste_a_institucion" type="checkbox" :checked="isFilled" :disabled="isFilled" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <label for="asiste" class="text-sm font-medium text-gray-700">Asiste a esta institución</label>
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-700 mb-1">Curso</label>
                    <input name="curso" :value="selected?.aula?.curso || ''" disabled placeholder="curso" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-700 mb-1">División</label>
                    <input name="division" :value="selected?.aula?.division || ''" disabled placeholder="división" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <div class="mt-4">
            <label class="text-sm font-medium text-gray-700 mb-1">Observaciones</label>
            <textarea name="observaciones" rows="3" class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('observaciones') }}</textarea>
        </div>

        <div class="fila-botones mt-8">
            <button type="submit" class="btn-aceptar">Guardar y Volver</button>
            <a href="{{ route('alumnos.crear-editar') }}" class="btn-volver">Volver</a>
        </div>
    </form>
</div>

<script>
    function familiarForm() {
        return {
            parentesco: '{{ old('parentesco','padre') }}',
            searchQuery: '',
            results: [],
            selected: null,
            get isFilled(){ return this.selected !== null; },
            field(key) {
                if (this.selected) {
                    if (key === 'dni') return this.selected.persona?.dni || '';
                    return this.selected.persona?.[key] || '';
                }
                const olds = @json(old());
                return olds[key] ?? '';
            },
            async search(){
                const q = this.searchQuery?.trim();
                if (!q) { this.results=[]; return; }
                try {
                    const res = await fetch('{{ route('alumnos.buscar') }}?q=' + encodeURIComponent(q));
                    if (!res.ok) return;
                    this.results = await res.json();
                } catch(e) { console.error(e); }
            },
            selectAlumno(al){ this.selected = al; this.results = []; this.searchQuery = al.persona?.dni || ''; }
        }
    }
</script>
@endsection