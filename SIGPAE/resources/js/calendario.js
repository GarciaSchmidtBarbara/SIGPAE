import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import esLocale from '@fullcalendar/core/locales/es';

document.addEventListener('DOMContentLoaded', function() {
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
            today: 'Hoy',
            month: 'Mes',
            list: 'Lista'
        },
        height: 350,
        contentHeight: 300,
        navLinks: false,
        editable: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: 2,
        weekends: true,
        dayHeaderFormat: { weekday: 'short' },
        titleFormat: { year: 'numeric', month: 'short' },
        
        // Cargar eventos desde el servidor
        events: function(info, successCallback, failureCallback) {
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
        dateClick: function(info) {
            const fecha = info.dateStr;
            alert(`Crear evento para: ${fecha}\n(Próximamente se abrirá un formulario)`);
            
            // TODO: Abrir modal para crear evento
            // const titulo = prompt('Título del evento:');
            // if (titulo) {
            //     crearEvento(fecha, titulo);
            // }
        },

        // Click en un evento existente
        eventClick: function(info) {
            const evento = info.event;
            alert(`Evento: ${evento.title}\n${evento.extendedProps.lugar || ''}\n${evento.extendedProps.notas || ''}`);
            
            // TODO: Abrir modal con detalles del evento
        },

        // Arrastrar y soltar eventos
        eventDrop: function(info) {
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
