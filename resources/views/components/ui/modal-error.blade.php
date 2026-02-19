@props([
  'title' => '',
  'message' => '',
  'variant' => 'error',    // error | success | info | warning
  'confirmText' => 'Aceptar',
])

@php
  $variants = [
    'error'   => ['circle' => 'bg-black text-white',     'title' => 'text-black',     'fallback' => 'ERROR'],
    'success' => ['circle' => 'bg-green-600 text-white', 'title' => 'text-green-700', 'fallback' => 'ÉXITO'],
    'info'    => ['circle' => 'bg-blue-600 text-white',  'title' => 'text-blue-700',  'fallback' => 'INFO'],
    'warning' => ['circle' => 'bg-amber-600 text-white', 'title' => 'text-amber-700', 'fallback' => 'ATENCIÓN'],
  ];
  $v = $variants[$variant] ?? $variants['info'];
  $displayTitle = $title !== '' ? strtoupper($title) : $v['fallback'];
@endphp

<x-ui.modal title="" size="md">
  <div class="px-6 py-4 text-center">
    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full {{ $v['circle'] }}">
      <span class="text-2xl leading-none">!</span>
    </div>

    <h3 class="mb-3 text-lg font-semibold tracking-wide {{ $v['title'] }}">
      {{ $displayTitle }}
    </h3>

    <p class="mx-auto max-w-[36ch] text-[20px] leading-snug text-gray-900">
      {{ $message }}
      {{-- si necesitás HTML en el mensaje, usá: {!! $message !!} --}}
    </p>
  </div>

  <x-slot:footer>
    <div class="w-full flex justify-end">
      <button class="btn-aceptar" @click="open=false">{{ $confirmText }}</button>
    </div>
  </x-slot:footer>
</x-ui.modal>
