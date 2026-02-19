@extends('layouts.base')

@section('encabezado', isset($evento) ? 'Editar Evento' : 'Crear Evento')

@section('contenido')
<div x-data="eventoForm({{ isset($evento) ? 'true' : 'false' }}, {{ isset($evento) && $evento->fecha_hora->isPast() ? 'true' : 'false' }})" class="space-y-6">
    <form method="POST" 
          action="{{ isset($evento) ? route('eventos.actualizar', $evento->id_evento) : route('eventos.guardar') }}"
          @submit.prevent="validarYGuardar">
        @csrf
        @if(isset($evento))
            @method('PUT')
        @endif

        <!-- Campos hidden para eventos finalizados -->
        <template x-if="esEventoFinalizado">
            <div>
                <input type="hidden" name="tipo_evento" :value="formData.tipo_evento">
                <input type="hidden" name="fecha_hora" :value="formData.fecha_hora">
                <input type="hidden" name="lugar" :value="formData.lugar">
                <input type="hidden" name="notas" :value="formData.notas">
            </div>
        </template>

        <!-- Información del Evento -->
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <h3 class="text-lg font-semibold text-primary border-b pb-2">Información del evento</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Tipo de evento -->
                <div>
                    <x-campo-requerido text="Tipo de evento" required />
                    <select name="tipo_evento" 
                            x-model="formData.tipo_evento"
                            :disabled="esEventoFinalizado"
                            class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            @change="limpiarError('tipo_evento')">
                        <option value="">Seleccione...</option>
                        <option value="BANDA">Acta de Banda</option>
                        <option value="RG">Reunión gabinete</option>
                        <option value="RD">Reunión directivos</option>
                        <option value="CITA_FAMILIAR">Cita familiar</option>
                    </select>
                    <div x-show="errors.tipo_evento" x-text="errors.tipo_evento" class="text-xs text-red-600 mt-1"></div>
                </div>
            </div>

            <!-- Fecha y Hora / Lugar -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-campo-requerido text="Fecha y hora" required />
                    <input type="datetime-local" 
                           name="fecha_hora"
                           x-model="formData.fecha_hora"
                           :disabled="esEventoFinalizado"
                           class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           @input="limpiarError('fecha_hora')">
                    <div x-show="errors.fecha_hora" x-text="errors.fecha_hora" class="text-xs text-red-600 mt-1"></div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lugar</label>
                    <input type="text" 
                           name="lugar"
                           x-model="formData.lugar"
                           :disabled="esEventoFinalizado"
                           placeholder="Ubicación del evento"
                           class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <!-- Profesionales -->
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <h3 class="text-lg font-semibold text-primary border-b pb-2">Profesionales</h3>
            
            <div class="space-y-2">
                <template x-for="(prof, index) in profesionales" :key="index">
                    <div class="flex items-center gap-4 p-2 border rounded">
                        <div class="flex-1">
                            <select :name="esEventoFinalizado ? '' : `profesionales[${index}][id]`"
                                    x-model="prof.id"
                                    :disabled="esEventoFinalizado"
                                    class="w-full border px-2 py-1 rounded">
                                <option value="">Seleccione profesional...</option>
                                @foreach($profesionalesDisponibles ?? [] as $p)
                                <option value="{{ $p->id_profesional }}">
                                    {{ $p->persona->nombre }} {{ $p->persona->apellido }} - {{ $p->siglas?->value }}
                                </option>
                                @endforeach
                            </select>
                            <!-- Campos hidden para eventos finalizados -->
                            <template x-if="esEventoFinalizado && prof.id">
                                <input type="hidden" :name="`profesionales[${index}][id]`" :value="prof.id">
                            </template>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="flex items-center gap-1">
                                <input type="checkbox" 
                                       :name="`profesionales[${index}][confirmado]`"
                                       x-model="prof.confirmado"
                                       :disabled="esEventoFinalizado"
                                       class="rounded">
                                <span class="text-sm">Confirmado</span>
                            </label>
                            <label class="flex items-center gap-1">
                                <input type="checkbox" 
                                       :name="`profesionales[${index}][asistio]`"
                                       x-model="prof.asistio"
                                       :disabled="!esEventoFinalizado"
                                       class="rounded">
                                <span class="text-sm">Asistió</span>
                            </label>
                        </div>
                        <button type="button" 
                                @click="profesionales.splice(index, 1)"
                                :disabled="esEventoFinalizado"
                                class="text-red-600 hover:text-red-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </template>
                <button type="button" 
                        @click="profesionales.push({ id: '', confirmado: false, asistio: false })"
                        :disabled="esEventoFinalizado"
                        class="btn-aceptar">
                    + Agregar profesional
                </button>
            </div>
        </div>

        <!-- Participantes (Cursos y Alumnos) -->
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <h3 class="text-lg font-semibold text-primary border-b pb-2">Participantes</h3>
            
            <!-- Cursos -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cursos</label>
                <div class="flex gap-2 mb-2" x-show="!esEventoFinalizado">
                    <select x-model="cursoSeleccionado" 
                            class="flex-1 border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Seleccione un curso...</option>
                        @foreach($cursos ?? [] as $curso)
                        <option value="{{ $curso->id_aula }}" x-text="'{{ $curso->descripcion }}'" data-descripcion="{{ $curso->descripcion }}"></option>
                        @endforeach
                    </select>
                    <button type="button" 
                            @click="agregarCurso()"
                            class="btn-aceptar">
                        + Agregar
                    </button>
                </div>
                
                <!-- Tabla de cursos seleccionados -->
                <div class="border rounded" x-show="cursosSeleccionados.length > 0">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-sm">Curso</th>
                                <th class="px-3 py-2 text-center text-sm" x-show="!esEventoFinalizado">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(curso, index) in cursosSeleccionados" :key="curso.id">
                                <tr class="border-t">
                                    <td class="px-3 py-2" x-text="curso.descripcion"></td>
                                    <td class="px-3 py-2 text-center" x-show="!esEventoFinalizado">
                                        <input type="hidden" :name="`cursos[${index}]`" :value="curso.id">
                                        <button type="button" 
                                                @click="cursosSeleccionados.splice(index, 1)"
                                                class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-gray-500 mt-1" x-show="cursosSeleccionados.length === 0">No hay cursos seleccionados</p>
            </div>

            <!-- Alumnos -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Alumnos</label>
                <div class="space-y-2">
                    <!-- Buscador de alumnos -->
                    <div class="flex items-end gap-3" x-show="!esEventoFinalizado">
                        <div class="flex-1">
                            <div class="relative">
                                <input type="text" 
                                       x-model.debounce.400ms="searchQuery" 
                                       placeholder="DNI / Nombre / Apellido"
                                       class="w-full border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                
                                <div x-show="resultadosAlumnos.length" 
                                     class="absolute z-10 mt-1 w-full bg-white border rounded shadow">
                                    <template x-for="alumno in resultadosAlumnos" :key="alumno.id_alumno">
                                        <button type="button"
                                                @click="agregarAlumno(alumno)"
                                                class="w-full text-left px-3 py-2 hover:bg-gray-100">
                                            <span x-text="alumno.persona.apellido + ', ' + alumno.persona.nombre"></span>
                                            <span class="text-xs text-gray-500" x-text="' - DNI ' + alumno.persona.dni"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-aceptar" @click="buscarAlumnos()">Buscar</button>
                    </div>

                    <!-- Lista de alumnos seleccionados -->
                    <div class="space-y-1">
                        <template x-for="(alumno, index) in alumnosSeleccionados" :key="index">
                            <div class="flex items-center justify-between p-2 border rounded">
                                <span x-text="`${alumno.persona.apellido}, ${alumno.persona.nombre}`"></span>
                                <div class="flex items-center gap-2">
                                    <label class="flex items-center gap-1" x-show="esEventoFinalizado">
                                        <input type="checkbox" 
                                               :name="`alumnos[${index}][asistio]`"
                                               x-model="alumno.asistio"
                                               class="rounded">
                                        <span class="text-sm">Asistió</span>
                                    </label>
                                    <input type="hidden" :name="`alumnos[${index}][id]`" :value="alumno.id_alumno">
                                    <button type="button" 
                                            @click="alumnosSeleccionados.splice(index, 1)"
                                            class="text-red-600 hover:text-red-800 text-lg">
                                        🗑️
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notas -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-primary border-b pb-2 mb-4">Notas</h3>
            <textarea name="notas" 
                      x-model="formData.notas"
                      :disabled="esEventoFinalizado"
                      rows="4"
                      class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                      placeholder="Observaciones o notas adicionales"></textarea>
        </div>

        <!-- Botones -->
        <div class="flex gap-4">
            <button type="submit" class="btn-aceptar">
                <span x-text="esEventoFinalizado ? 'Guardar cambios' : 'Guardar'"></span>
            </button>
            <a href="{{ route('eventos.principal') }}" class="btn-eliminar">Cancelar</a>
        </div>
    </form>
</div>

<script>
function eventoForm(esEdicion = false, esFinalizado = false) {
    return {
        esEventoFinalizado: esFinalizado,
        formData: {
            tipo_evento: '{{ old('tipo_evento', $evento->tipo_evento?->value ?? '') }}',
            fecha_hora: '{{ old('fecha_hora', isset($evento) ? $evento->fecha_hora->format('Y-m-d\TH:i') : '') }}',
            lugar: '{{ old('lugar', $evento->lugar ?? '') }}',
            notas: '{{ old('notas', $evento->notas ?? '') }}'
        },
        profesionales: @json(old('profesionales', $profesionalesEvento ?? [])),
        alumnosSeleccionados: @json(old('alumnos', $alumnosEvento ?? [])),
        cursosSeleccionados: [],
        cursoSeleccionado: '',
        searchQuery: '',
        resultadosAlumnos: [],
        errors: {
            tipo_evento: '',
            fecha_hora: ''
        },
        
        init() {
            // Cargar cursos del evento si existe
            @if(isset($cursosEvento) && count($cursosEvento) > 0)
                const cursosEventoIds = @json($cursosEvento);
                const todosCursos = @json($cursos->map(fn($c) => ['id' => $c->id_aula, 'descripcion' => $c->descripcion]));
                this.cursosSeleccionados = todosCursos.filter(c => cursosEventoIds.includes(c.id));
            @endif
            
            // Watch para búsqueda en tiempo real
            this.$watch('searchQuery', () => {
                this.buscarAlumnos();
            });
        },

        agregarCurso() {
            if (!this.cursoSeleccionado) return;
            
            const select = document.querySelector('select[x-model="cursoSeleccionado"]');
            const option = select.options[select.selectedIndex];
            const curso = {
                id: parseInt(this.cursoSeleccionado),
                descripcion: option.getAttribute('data-descripcion')
            };
            
            // Verificar que no esté duplicado
            if (!this.cursosSeleccionados.find(c => c.id === curso.id)) {
                this.cursosSeleccionados.push(curso);
            }
            
            this.cursoSeleccionado = '';
        },
        
        async buscarAlumnos() {
            const q = this.searchQuery ? this.searchQuery.trim() : '';
            
            if (!q) {
                this.resultadosAlumnos = [];
                return;
            }

            try {
                const response = await fetch(`/api/alumnos/buscar?q=${encodeURIComponent(this.searchQuery)}`);
                const data = await response.json();
                this.resultadosAlumnos = data.filter(a => 
                    !this.alumnosSeleccionados.find(sel => sel.id_alumno === a.id_alumno)
                );
            } catch (error) {
                console.error('Error buscando alumnos:', error);
            }
        },

        agregarAlumno(alumno) {
            this.alumnosSeleccionados.push({ ...alumno, asistio: false });
            this.searchQuery = '';
            this.resultadosAlumnos = [];
        },

        limpiarError(campo) {
            this.errors[campo] = '';
        },

        validarYGuardar(event) {
            this.errors = { tipo_evento: '', fecha_hora: '' };
            let hayError = false;

            if (!this.formData.tipo_evento) {
                this.errors.tipo_evento = 'Debe seleccionar un tipo de evento';
                hayError = true;
            }

            if (!this.formData.fecha_hora) {
                this.errors.fecha_hora = 'Debe ingresar fecha y hora';
                hayError = true;
            }

            if (!hayError) {
                event.target.closest('form').submit();
            }
        }
    }
}
</script>
@endsection
