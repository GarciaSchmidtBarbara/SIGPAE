import './bootstrap';
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
// Función para manejar la selección de radio buttons
function handleRadioSelection() {
  // Buscar todos los grupos de radio buttons personalizados en el documento
  document.querySelectorAll('.custom-radio-group input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function () {
      // Obtener el nombre del grupo de radio buttons
      const groupName = this.getAttribute('name');

      // Encontrar todos los radio buttons del mismo grupo
      const radios = document.querySelectorAll(`input[name="${groupName}"]`);

      // Limpiar la selección previa
      radios.forEach(r => {
        r.parentElement.classList.remove('selected');
      });

      // Marcar el seleccionado
      if (this.checked) {
        this.parentElement.classList.add('selected');

        // Emitir un evento personalizado con los detalles de la selección
        const event = new CustomEvent('radio-changed', {
          detail: {
            group: groupName,
            value: this.value,
            element: this
          }
        });
        document.dispatchEvent(event);
      }
    });
  });
}

// Ejecutar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
  handleRadioSelection();
});

// Exportar la función para usarla en otros archivos si es necesario
window.handleRadioSelection = handleRadioSelection;


//JS PARA DESCARGAR TABLAS 
document.addEventListener('DOMContentLoaded', function() {
    // 1. Escucha los clics en cualquier botón con la clase 'btn-print-table'
    document.querySelectorAll('.btn-print-table').forEach(button => {
        button.addEventListener('click', function() {
            
            // 2. Busca la tabla más cercana o un contenedor específico con la clase 'data-table-to-print'
            // O, si sabes que la tabla siempre está en la misma posición, usa un selector directo.
            // Para ser más robustos, buscaremos la tabla dentro del contenedor principal de la vista.
            const contenidoTabla = document.querySelector('.data-table-to-print');
            
            if (contenidoTabla) {
                const ventanaImpresion = window.open('', '', 'height=600,width=800');
                
                ventanaImpresion.document.write('<html><head><title>Imprimir Lista</title>');
                
                // ** IMPORTANTE: Aquí puedes usar una variable para el título **
                const tituloPagina = document.querySelector('.page-title-print') ? 
                                     document.querySelector('.page-title-print').textContent : 
                                     'Reporte Impreso';

                ventanaImpresion.document.write('<style>');
                ventanaImpresion.document.write(`
                    body { font-family: sans-serif; margin: 20px; }
                    h1 { text-align: center; margin-bottom: 20px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    
                    /* Oculta cualquier elemento con la clase 'no-print' o la columna de acciones */
                    .no-print, .accion-col, table thead th:last-child { 
                        display: none !important; 
                    } 
                `);
                
                ventanaImpresion.document.write('</style>');
                ventanaImpresion.document.write('</head><body>');
                ventanaImpresion.document.write('<h1>' + tituloPagina + '</h1>');
                ventanaImpresion.document.write(contenidoTabla.outerHTML); 
                ventanaImpresion.document.write('</body></html>');
                ventanaImpresion.document.close();
                
                ventanaImpresion.print();

                // CIERRE AUTOMÁTICO DE LA VENTANA DESPUÉS DE UN BREVE RETRASO
                // 10 milisegundos (ms) suelen ser suficientes para que el diálogo de impresión se lance, 
                // permitiendo que el navegador gestione la impresión o la cancelación, y luego la cierre.
                setTimeout(() => { 
                    ventanaImpresion.close(); 
                }, 10); 
            } else {
                alert('No se encontró el contenido para imprimir (clase .data-table-to-print).');
            }
        });
    });
});