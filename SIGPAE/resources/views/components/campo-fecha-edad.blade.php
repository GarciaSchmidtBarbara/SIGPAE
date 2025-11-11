@props([
    'label' => 'Fecha de nacimiento',
    'name' => 'fecha_nacimiento',
    'value' => '',
    'edadName' => 'edad',
    'edadValue' => '',
    'required' => false,
])

<div 
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
    class="flex gap-6 items-end"
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
            x-model="fechaNacimiento"
            @change="calcularEdad"
            class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
            value="{{ $value }}"
            {{ $required ? 'required' : '' }}
        >
    </div>

    <!-- Campo de edad -->
    <div class="flex flex-col w-1/4">
        <label class="text-sm font-medium text-gray-700 mb-1">Edad</label>
        <input 
            type="number"
            name="{{ $edadName }}"
            x-model="edad"
            readonly
            class="border px-2 py-1 rounded bg-gray-100 text-gray-700"
        >
    </div>
</div>
