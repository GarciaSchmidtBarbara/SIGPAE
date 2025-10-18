@extends('layouts.base')
@section('encabezado', 'Plan de acción N°')

@push('scripts')
<script>
  // Función principal
  function initializeRadioVisibility() {
    console.log('Initializing radio visibility...'); // Debug
    console.log('DOMContentLoaded event fired'); // Debug

    // Obtener elementos
    const radios = document.querySelectorAll('input[name="radio_group"]');
    console.log('Radios encontrados:', radios.length); // Debug

    function actualizarVisibilidad() {
      // Encontrar el radio button seleccionado
      const radioSeleccionado = Array.from(radios).find(radio => radio.checked);
      console.log('Radio seleccionado:', radioSeleccionado?.value); // Debug
      
      // Obtener todos los elementos que necesitan actualizarse
      const elementos = document.querySelectorAll('[data-visibility-toggle]');
      console.log('Elementos para toggle:', elementos.length); // Debug

      elementos.forEach(elemento => {
        // Obtener las condiciones de visibilidad del elemento
        const condiciones = elemento.dataset.visibilityToggle.split(',').map(c => c.trim());
        
        // Si no hay radio seleccionado, ocultar todo
        if (!radioSeleccionado) {
          elemento.classList.add('hidden');
          return;
        }

        // Si las condiciones incluyen "always" o el valor seleccionado, mostrar
        if (condiciones.includes('always') || condiciones.includes(radioSeleccionado.value)) {
          elemento.classList.remove('hidden');
          console.log('Mostrando:', elemento.id); // Debug
        } else {
          elemento.classList.add('hidden');
          console.log('Ocultando:', elemento.id); // Debug
        }
      });
    }

    // Agregar event listeners a todos los radio buttons
    radios.forEach(radio => {
      radio.addEventListener('change', actualizarVisibilidad);
    });

    // Ejecutar al cargar la página
    actualizarVisibilidad();
  }

  // Intentar inicializar inmediatamente
  initializeRadioVisibility();

  // Si falla, intentar cuando el DOM esté listo
  document.addEventListener('DOMContentLoaded', initializeRadioVisibility);

  // Si aún falla, intentar después de una breve espera
  setTimeout(initializeRadioVisibility, 500);
</script>
@endpush


@section('contenido')
  <div class="space-y-6">
    <div class="space-y-8">
      <p>Estado: Abierto </p>
    </div>
    
    <div class="space-y-8">
      <p>TIPO </p>
      @php
        $tipoPlan = ['Institucional', 'Individual', 'Grupal'];
      @endphp
      <!-- Sección de radio buttons horizontal -->
      <div class="space-y-4">
          <x-opcion-unica :items="$tipoPlan" name="radio_group" layout="horizontal" />
      </div>
    </div>

    <!-- Campos de texto comunes -->
    <div id="campo-texto" class="hidden" data-visibility-toggle="always">
      <p>DESCRIPCIÓN</p>
      <div class="space-y-2 mb-6">
        <label class="block text-sm font-medium text-gray-700">
          Objetivos <span class="text-red-500">*</span>
        </label>
        <textarea class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" name="objetivos" rows="4"></textarea>
        <label class="block text-sm font-medium text-gray-700">
          Acciones a realizar <span class="text-red-500">*</span>
        </label>
        <textarea class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" name="acciones" rows="2"></textarea>
        <label class="block text-sm font-medium text-gray-700">
          Observaciones <span class="text-red-500">*</span>
        </label>
        <textarea class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" name="observaciones" rows="2"></textarea>
        <label class="block text-base font-medium text-gray-700">
          Documentos
        </label>
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <button class="btn-subir">Examinar</button>
            <span class="text-sm text-gray-500">Solo archivos en formato pdf, jpeg, png o doc con menos de 100Kb</span>
          </div>
        </div>
        <div class="space-y-2">
          <p class="text-sm font-medium text-gray-700">Cargados:</p>
          <div class="space-y-2">
            <!-- Placeholder para archivos cargados -->
            <div class="flex items-center justify-between p-2 bg-gray-100 rounded-md">
              <span class="text-sm text-gray-600">Documento1.pdf</span>
              <button class="text-gray-500 hover:text-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
            <div class="flex items-center justify-between p-2 bg-gray-100 rounded-md">
              <span class="text-sm text-gray-600">Documento2.pdf</span>
              <button class="text-gray-500 hover:text-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- DESTINATARIOS -->
    <div id="destinatarios" class="hidden" data-visibility-toggle="Individual">
      <div class="space-y-10 mb-6">
        <p>DESTINATARIOS</p>
        <div class="fila-botones">
          <button class="btn-aceptar">Alumno busqueda</button>
          <button class="btn-aceptar">Documento busqueda</button>
        </div>
        <div class="space-y-2">
          <p class="font-semibold">Información personal del alumno</p>
          <div class="grid grid-cols-4 gap-4">
            <div>
              <p><span class="font-semibold">Nombre y Apellido:</span> Juan Pérez</p>
              <p><span class="font-semibold">Nacionalidad:</span> Argentina</p>
            </div>
            <div>
              <p><span class="font-semibold">DNI:</span> 12345678</p>
              <p><span class="font-semibold">Domicilio:</span> Calle Falsa 123</p>
            </div>
            <div>
              <p><span class="font-semibold">Fecha de nacimiento:</span> 01/01/2000</p>
              <p><span class="font-semibold">Edad:</span> 10</p>
            </div>
            <div>
              <p><span class="font-semibold">Curso:</span> 3°A</p>
            </div>

        </div>
      </div>
    </div>

    <!-- RESPONSABLES -->
    <div id="responsables" class="hidden" data-visibility-toggle="Individual,Grupal">
      <div class="space-y-10 mb-6">
        <p>RESPONSABLES</p>
        <div class="space-y-4">
          <label class="block text-sm font-medium text-gray-700">
            Profesionales <span class="text-red-500">*</span>
          </label>
          <div class="fila-botones">
            <button class="btn-aceptar">Buscar profesional</button>
            <button class="btn-aceptar">Agregar profesional</button>
          </div>
          <!-- Lista de profesionales seleccionados iría aquí -->
        </div>
      </div>
    </div>

    <div class="fila-botones mt-8">
      <button class="btn-aceptar">Marcar como cerrado</button>
      <button class="btn-aceptar">Guardar</button>
      <button class="btn-eliminar">Eliminar</button>
      <a class="btn-volver" href="{{ route('planDeAccion.principal') }}" >Volver</a>
    </div>
  </div>


@endsection