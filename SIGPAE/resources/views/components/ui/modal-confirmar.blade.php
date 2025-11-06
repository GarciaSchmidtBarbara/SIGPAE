@props([
  'message' => 'Are you sure?',
  'confirmText' => 'Confirm',
  'cancelText' => 'Cancel',
  'formId' => null,            // if set, submit this form on confirm
  'event' => 'confirm-accepted',// if no formId, dispatch this event
  'centerButtons' => false,     // center both buttons
])

<x-ui.modal title="" size="md">
  <div class="px-6 py-6">
    <p class="text-center text-[20px] leading-snug text-gray-900">
      {{ $message }}
    </p>
  </div>

  <x-slot:footer>
    <div class="w-full flex justify-between items-center gap-10">
      <button class="btn-aceptar"
        @click="
          @if($formId)
            document.getElementById('{{ $formId }}')?.submit();
          @else
            $dispatch('{{ $event }}');
          @endif
          open=false
        ">
        {{ $confirmText }}
      </button>

      <button class="btn-eliminar" @click="open=false">
        {{ $cancelText }}
      </button>
    </div>
  </x-slot:footer>
</x-ui.modal>
