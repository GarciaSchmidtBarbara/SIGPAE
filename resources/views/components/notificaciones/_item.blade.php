<li class="{{ $notif->leida ? 'bg-white' : 'bg-indigo-50' }} hover:bg-gray-50 transition-colors">
    <form method="POST" action="{{ route('notificaciones.leer', $notif->id_notificacion) }}">
        @csrf
        <button type="submit" class="w-full text-left px-4 sm:px-6 py-3 flex gap-3 items-start">

            <span class="mt-0.5 shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100">
                <i class="fas {{ $notif->tipo->icono() }} {{ $notif->tipo->iconoColor() }} text-sm"></i>
            </span>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        {{ $notif->tipo->etiqueta() }}
                    </span>
                    @unless ($notif->leida)
                        <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
                    @endunless
                </div>

                <p class="text-sm text-gray-700 mt-0.5 leading-snug">{{ $notif->mensaje }}</p>

                @if ($notif->recursoBorrado())
                    <span class="inline-block mt-1 text-xs text-red-500 font-medium">
                        <i class="fas fa-exclamation-circle mr-0.5"></i> El recurso fue eliminado
                    </span>
                @elseif ($notif->urlDestino())
                    <span class="inline-block mt-1 text-xs text-indigo-600 font-medium hover:underline">
                        Ver <i class="fas fa-arrow-right text-[10px]"></i>
                    </span>
                @endif

                <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>

                @if ($notif->tipo === \App\Enums\TipoNotificacion::RECORDATORIO_DERIVACION)
                    <button
                        type="button"
                        onclick="dejarDeRecordar({{ $notif->fk_id_evento }}, this)"
                        class="mt-1.5 inline-flex items-center gap-1 text-xs text-orange-600 hover:text-orange-800 font-medium hover:underline focus:outline-none"
                    >
                        <i class="fas fa-bell-slash text-[11px]"></i> Dejar de recordar
                    </button>
                @endif
            </div>
        </button>
    </form>
</li>
