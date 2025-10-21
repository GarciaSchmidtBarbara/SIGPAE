import './bootstrap';

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