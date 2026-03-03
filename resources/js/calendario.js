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
        editable: false,
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

    });

    calendar.render();
});
