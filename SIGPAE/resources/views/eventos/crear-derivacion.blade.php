@extends('layouts.base')

@section('encabezado', 'Crear Derivación Externa')

@section('contenido')
<div x-data="derivacionForm()" class="space-y-6">
    <form method="POST" action="{{ route('eventos.guardar-derivacion') }}" @submit.prevent="validarYGuardar">
        @csrf

        <!-- Detalles de la derivación externa -->
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <h3 class="text-lg font-semibold text-primary border-b pb-2">Detalles de la derivación externa</h3>
            
            <div>
                <x-campo-requerido text="Descripción externa" required />
                <textarea name="descripcion_externa" 
                          x-model="formData.descripcion"
                          rows="3"
                          placeholder="Detalles de la derivación..."
                          class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                          @input="limpiarError('descripcion')"></textarea>
                <div x-show="errors.descripcion" x-text="errors.descripcion" class="text-xs text-red-600 mt-1"></div>
            </div>
        </div>

        <!-- Agendar recordatorio -->
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <h3 class="text-lg font-semibold text-primary border-b pb-2">Agendar recordatorio</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                    <input type="date" 
                           name="fecha"
                           x-model="formData.fecha"
                           class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lugar</label>
                    <input type="text" 
                           name="lugar"
                           x-model="formData.lugar"
                           placeholder="Ubicación"
                           class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Profesional tratante</label>
                <input type="text" 
                       name="profesional_tratante"
                       x-model="formData.profesional_tratante"
                       placeholder="Nombre del profesional externo"
                       class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Enviar recordatorio cada:</label>
                <div class="flex items-center gap-2">
                    <input type="number" 
                           name="periodo_recordatorio"
                           x-model="formData.periodo_recordatorio"
                           min="1"
                           placeholder="1"
                           class="w-24 border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span>semana(s)</span>
                </div>
            </div>
        </div>

        <!-- Participantes -->
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <h3 class="text-lg font-semibold text-primary border-b pb-2">Participantes</h3>
            
            <!-- Buscador de alumnos -->
            <div class="relative">
                <input type="text" 
                       x-model.debounce.400ms="searchQuery" 
                       @input="buscarAlumnos()"
                       placeholder="Buscar alumno por DNI, nombre o apellido"
                       class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                
                <div x-show="resultadosAlumnos.length > 0" 
                     class="absolute z-10 mt-1 w-full bg-white border rounded shadow-lg max-h-48 overflow-y-auto">
                    <template x-for="alumno in resultadosAlumnos" :key="alumno.id_alumno">
                        <button type="button"
                                @click="agregarAlumno(alumno)"
                                class="w-full text-left px-3 py-2 hover:bg-gray-100">
                            <span x-text="`${alumno.persona.apellido}, ${alumno.persona.nombre} - DNI: ${alumno.persona.dni}`"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Tabla de alumnos seleccionados -->
            <div x-show="alumnosSeleccionados.length > 0" class="border rounded">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm">DNI</th>
                            <th class="px-4 py-2 text-left text-sm">Nombre</th>
                            <th class="px-4 py-2 text-left text-sm">Apellido</th>
                            <th class="px-4 py-2 text-left text-sm">Edad</th>
                            <th class="px-4 py-2 text-left text-sm">Curso</th>
                            <th class="px-4 py-2 text-center text-sm">Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(alumno, index) in alumnosSeleccionados" :key="index">
                            <tr class="border-t">
                                <td class="px-4 py-2 text-sm" x-text="alumno.persona.dni"></td>
                                <td class="px-4 py-2 text-sm" x-text="alumno.persona.nombre"></td>
                                <td class="px-4 py-2 text-sm" x-text="alumno.persona.apellido"></td>
                                <td class="px-4 py-2 text-sm" x-text="calcularEdad(alumno.persona.fecha_nacimiento)"></td>
                                <td class="px-4 py-2 text-sm" x-text="alumno.aula?.descripcion || 'N/A'"></td>
                                <td class="px-4 py-2 text-center">
                                    <button type="button" 
                                            @click="alumnosSeleccionados.splice(index, 1)"
                                            class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                                <input type="hidden" :name="`alumnos[${index}]`" :value="alumno.id_alumno">
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Notas -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-primary border-b pb-2 mb-4">Notas</h3>
            <textarea name="notas" 
                      x-model="formData.notas"
                      rows="4"
                      class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                      placeholder="Observaciones adicionales"></textarea>
        </div>

        <!-- Botones -->
        <div class="flex gap-4">
            <button type="submit" class="btn-aceptar">Guardar</button>
            <button type="button" class="btn-eliminar" @click="window.history.back()">Eliminar</button>
            <a href="{{ route('eventos.principal') }}" class="btn-volver">Volver</a>
        </div>
    </form>
</div>

<script>
function derivacionForm() {
    return {
        formData: {
            descripcion: '',
            fecha: '',
            lugar: '',
            profesional_tratante: '',
            periodo_recordatorio: 1,
            notas: ''
        },
        alumnosSeleccionados: [],
        searchQuery: '',
        resultadosAlumnos: [],
        errors: {
            descripcion: ''
        },

        async buscarAlumnos() {
            if (!this.searchQuery || this.searchQuery.length < 2) {
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
            this.alumnosSeleccionados.push(alumno);
            this.searchQuery = '';
            this.resultadosAlumnos = [];
        },

        calcularEdad(fechaNacimiento) {
            if (!fechaNacimiento) return 'N/A';
            const hoy = new Date();
            const nacimiento = new Date(fechaNacimiento);
            let edad = hoy.getFullYear() - nacimiento.getFullYear();
            const mes = hoy.getMonth() - nacimiento.getMonth();
            if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
                edad--;
            }
            return edad;
        },

        limpiarError(campo) {
            this.errors[campo] = '';
        },

        validarYGuardar(event) {
            this.errors = { descripcion: '' };
            let hayError = false;

            if (!this.formData.descripcion || this.formData.descripcion.trim() === '') {
                this.errors.descripcion = 'Debe ingresar una descripción';
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
