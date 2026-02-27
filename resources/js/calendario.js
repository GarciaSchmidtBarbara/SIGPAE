import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import esLocale from '@fullcalendar/core/locales/es';

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');

    if (!calendarEl) return;

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, interactionPlugin, listPlugin],
        locale: esLocale,
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: 'today'
        },
        buttonText: {
            today: 'Mes actual',
            month: 'Mes',
            list: 'Lista'
        },
        height: 320,
        contentHeight: 260,
        aspectRatio: 1.35,
        navLinks: false,
        editable: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: 3,
        weekends: true,
        dayHeaderFormat: { weekday: 'short' },
        titleFormat: function (date) {
            const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            return `${months[date.date.month]} ${date.date.year}`;
        },
        displayEventTime: false,
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            meridiem: false
        },

        // Cargar eventos desde el servidor
        events: function (info, successCallback, failureCallback) {
            fetch(`/eventos/calendario?start=${info.startStr}&end=${info.endStr}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    successCallback(data);
                })
                .catch(error => {
                    console.error('Error cargando eventos:', error);
                    failureCallback(error);
                });
        },

        // Click en un día para crear evento
        dateClick: function (info) {
            const fecha = info.dateStr;
            const eventosDelDia = calendar.getEvents().filter(evento => {
                const fechaEvento = evento.start.toISOString().split('T')[0];
                return fechaEvento === fecha;
            });

            // Disparar evento personalizado
            window.dispatchEvent(new CustomEvent('mostrar-eventos-dia', {
                detail: {
                    fecha: fecha,
                    eventos: eventosDelDia.map(e => ({
                        id: e.id,
                        title: e.title,
                        hora: e.extendedProps?.hora || '',
                        extendedProps: e.extendedProps || {}
                    }))
                }
            }));
        },

        // Click en un evento existente
        eventClick: function (info) {
            const evento = info.event;

            // Disparar evento personalizado para el modal
            window.dispatchEvent(new CustomEvent('mostrar-detalle-evento', {
                detail: {
                    id: evento.id,
                    title: evento.title,
                    tipo: evento.extendedProps?.tipo || '',
                    hora: evento.extendedProps?.hora || '',
                    lugar: evento.extendedProps?.lugar || '',
                    creador: evento.extendedProps?.creador || '',
                    notas: evento.extendedProps?.notas || ''
                }
            }));
        },

        // Arrastrar y soltar eventos
        eventDrop: function (info) {
            const evento = info.event;
            const nuevaFecha = evento.start.toISOString();

            // TODO: Actualizar evento en el servidor
            console.log(`Evento ${evento.id} movido a ${nuevaFecha}`);

            // if (!confirm(`¿Mover "${evento.title}" a ${nuevaFecha}?`)) {
            //     info.revert();
            // } else {
            //     actualizarEvento(evento.id, { fecha_hora: nuevaFecha });
            // }
        }
    });

    calendar.render();

    // Función helper para crear evento  (Hacer)
    function crearEvento(fecha, titulo) {
        fetch('/eventos', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                fecha_hora: fecha,
                tipo_evento: 'general',
                lugar: '',
                notas: titulo
            })
        })
            .then(response => response.json())
            .then(data => {
                calendar.refetchEvents();
                alert('Evento creado correctamente');
            })
            .catch(error => {
                console.error('Error creando evento:', error);
                alert('Error al crear el evento');
            });
    }

    // Función helper para actualizar evento (hacer...)
    function actualizarEvento(id, data) {
        fetch(`/eventos/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(data => {
                calendar.refetchEvents();
            })
            .catch(error => {
                console.error('Error actualizando evento:', error);
            });
    }
});
