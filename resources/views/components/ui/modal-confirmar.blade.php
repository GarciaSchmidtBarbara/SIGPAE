@props([
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'event' => 'confirm-accepted', // si no hay formId, se dispara este event
    'centerButtons' => false
])

<div x-data="{ open: false, formId: null, message: '' }"
     @abrir-modal-confirmar.window="
        formId = $event.detail.formId;
        message = $event.detail.message;
        open = true;
     "
>
    <x-ui.modal title="" size="md" x-show="open" @click.away="open=false">
        <div class="px-6 py-6">
            <p class="text-center text-[20px] leading-snug text-gray-900" x-text="message"></p>
        </div>

        <x-slot:footer>
            <div class="w-full flex justify-between items-center gap-10">
                <button class="btn-aceptar"
                    @click="
                        if(formId){
                            document.getElementById(formId)?.submit();
                        } else {
                            $dispatch('{{ $event }}');
                        }
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
</div>