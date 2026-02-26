{{--
  Componente: <x-notificaciones.campana />
  Muestra el ícono de campana con badge de no leídas.
  Al hacer clic abre un dropdown con la lista de notificaciones.
  Usa Alpine.js (ya incluído en el layout base) y el endpoint /notificaciones.
--}}
<div
    x-data="{
        open: false,
        notificaciones: [],
        noLeidas: 0,
        cargando: false,

        async cargar() {
            this.cargando = true;
            try {
                const res  = await fetch('{{ route('notificaciones.index') }}', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.notificaciones = data.notificaciones;
                this.noLeidas       = data.no_leidas;
            } finally {
                this.cargando = false;
            }
        },

        async marcarTodasLeidas() {
            await fetch('{{ route('notificaciones.leer-todas') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                }
            });
            this.notificaciones.forEach(n => n.leida = true);
            this.noLeidas = 0;
        },
    }"
    x-init="cargar()"
    @click.outside="open = false"
    class="relative"
>
    {{-- ── Botón campana ──────────────────────────────────────────────── --}}
    <button
        @click="open = !open"
        class="relative p-2 text-gray-500 hover:text-indigo-600 hover:bg-gray-100 rounded-md transition-colors focus:outline-none"
        aria-label="Notificaciones"
    >
        <i class="fas fa-bell text-lg"></i>

        {{-- Badge de no leídas --}}
        <span
            x-show="noLeidas > 0"
            x-text="noLeidas > 99 ? '99+' : noLeidas"
            class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] flex items-center justify-center
                   bg-red-500 text-white text-[10px] font-bold rounded-full px-1 leading-none"
        ></span>
    </button>

    {{-- ── Dropdown ────────────────────────────────────────────────────── --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-96 bg-white rounded-xl shadow-xl border border-gray-200 z-50 overflow-hidden"
        style="display:none"
    >
        {{-- Cabecera --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-gray-50">
            <span class="font-semibold text-gray-700 text-sm">Notificaciones</span>
            <button
                @click="marcarTodasLeidas()"
                x-show="noLeidas > 0"
                class="text-xs text-indigo-600 hover:underline focus:outline-none"
            >
                Marcar todas como leídas
            </button>
        </div>

        {{-- Lista --}}
        <ul class="max-h-[26rem] overflow-y-auto divide-y divide-gray-100">

            {{-- Estado: cargando --}}
            <template x-if="cargando">
                <li class="flex items-center justify-center py-8 text-gray-400 text-sm">
                    <i class="fas fa-circle-notch fa-spin mr-2"></i> Cargando…
                </li>
            </template>

            {{-- Estado: sin notificaciones --}}
            <template x-if="!cargando && notificaciones.length === 0">
                <li class="flex flex-col items-center py-10 text-gray-400 text-sm gap-2">
                    <i class="fas fa-bell-slash text-2xl"></i>
                    <span>No tenés notificaciones</span>
                </li>
            </template>

            {{-- Ítems --}}
            <template x-for="n in notificaciones" :key="n.id">
                <li
                    :class="n.leida ? 'bg-white' : 'bg-indigo-50'"
                    class="group hover:bg-gray-50 transition-colors"
                >
                    <form
                        :action="`/notificaciones/${n.id}/leer`"
                        method="POST"
                        @submit.prevent="
                            n.leida = true;
                            if (noLeidas > 0) noLeidas--;
                            if (n.url) window.location.href = n.url;
                            else $el.submit();
                        "
                    >
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-3 flex gap-3 items-start">

                            {{-- Ícono del tipo --}}
                            <span class="mt-0.5 shrink-0 w-7 h-7 flex items-center justify-center rounded-full bg-gray-100">
                                <i :class="'fas ' + n.icono + ' ' + n.icono_color + ' text-sm'"></i>
                            </span>

                            <div class="flex-1 min-w-0">
                                {{-- Etiqueta + punto no leído --}}
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide" x-text="n.etiqueta"></span>
                                    <span x-show="!n.leida" class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
                                </div>

                                {{-- Mensaje --}}
                                <p class="text-sm text-gray-700 mt-0.5 leading-snug" x-text="n.mensaje"></p>

                                {{-- Aviso si el recurso fue borrado --}}
                                <span
                                    x-show="n.recurso_borrado"
                                    class="inline-block mt-1 text-xs text-red-500 font-medium"
                                >
                                    <i class="fas fa-exclamation-circle mr-0.5"></i> El recurso fue eliminado
                                </span>

                                {{-- Enlace "Ver" si el recurso existe --}}
                                <span
                                    x-show="!n.recurso_borrado && n.url"
                                    class="inline-block mt-1 text-xs text-indigo-600 font-medium group-hover:underline"
                                >
                                    Ver <i class="fas fa-arrow-right text-[10px]"></i>
                                </span>

                                {{-- Fecha relativa --}}
                                <p class="text-xs text-gray-400 mt-1" x-text="n.fecha"></p>
                            </div>
                        </button>
                    </form>
                </li>
            </template>
        </ul>

        {{-- Pie --}}
        <div class="px-4 py-2 border-t border-gray-100 bg-gray-50 text-center">
            <span class="text-xs text-gray-400">
                Mostrando <span x-text="notificaciones.length"></span> notificaciones
            </span>
        </div>
    </div>
</div>
