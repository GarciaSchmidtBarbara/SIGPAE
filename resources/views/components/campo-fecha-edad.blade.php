@props([
    'label' => 'Fecha de nacimiento',
    'name' => 'fecha_nacimiento',
    'value' => '',
    'edadName' => 'edad',
    'edadValue' => '',
    'required' => false,
    // NUEVOS PROPS: Permiten cambiar qué variable de Alpine usan los inputs
    'modelFecha' => 'fechaNacimiento', 
    'modelEdad' => 'edad',
    'condicionReadonly' => 'false',
])

<fieldset 
    {{-- 
       LÓGICA INTELIGENTE:
       Solo cargamos el x-data por defecto si NO nos pasaron uno desde afuera.
       Esto evita el conflicto de "doble cerebro".
    --}}
    @if(!$attributes->has('x-data'))
        x-data="{
            fechaNacimiento: '{{ $value }}',
            edad: '{{ $edadValue }}',
            calcularEdad() {
                if (!this.fechaNacimiento) { this.edad = ''; return; }
                const hoy = new Date();
                const nacimiento = new Date(this.fechaNacimiento);
                let edad = hoy.getFullYear() - nacimiento.getFullYear();
                const mes = hoy.getMonth() - nacimiento.getMonth();
                if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
                    edad--;
                }
                this.edad = edad >= 0 ? edad : '';
            }
        }"
        x-init="calcularEdad"
    @endif

    {{-- Fusionamos atributos (aquí entrará el x-data externo si lo mandamos) --}}
    {{ $attributes->merge(['class' => 'flex gap-6 items-start border-0 p-0 m-0 min-w-0']) }}
>
    <!-- Campo de fecha -->
    <div class="flex flex-col w-1/2">
        <label class="text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
        <input 
            type="date"
            name="{{ $name }}"
            {{-- USAMOS LA VARIABLE DINÁMICA --}}
            x-model="{{ $modelFecha }}"
            x-bind:readonly="{{ $condicionReadonly }}"

            {{-- Esto establece la fecha máxima a "HOY" usando JS --}}
            :max="new Date().toISOString().split('T')[0]"
            
            {{-- Si usamos lógica externa, usamos el evento @change externo --}}
            @if(!$attributes->has('x-data'))
                @change="calcularEdad"
            @endif

            class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500
                disabled:bg-gray-100 disabled:cursor-not-allowed read-only:bg-gray-100 read-only:cursor-not-allowed"
            {{ $required ? 'required' : '' }}
        >
        <div class="mt-1">
            {{ $slot }}
        </div>
    </div>

    <!-- Campo de edad -->
    <div class="flex flex-col w-1/4">
        <label class="text-sm font-medium text-gray-700 mb-1">Edad</label>
        <input 
            type="number"
            name="{{ $edadName }}"
            {{-- USAMOS LA VARIABLE DINÁMICA --}}
            x-model="{{ $modelEdad }}"
            readonly
            class="border px-2 py-1 rounded bg-gray-100 text-gray-700
                disabled:bg-gray-100 disabled:cursor-not-allowed read-only:bg-gray-100 read-only:cursor-not-allowed"
        >
    </div>
</fieldset>