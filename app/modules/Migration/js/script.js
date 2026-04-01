/**
 * JavaScript del Módulo Migration
 */

$(document).ready(function() {
    console.log('Módulo Migration cargado correctamente');

    // Inicializar DataTable si existe
    if ($.fn.DataTable) {
        $('#dataTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            responsive: true,
            pageLength: 10
        });
    }

    // Ejemplo de función para llamadas AJAX al controlador
    function callController(funcion, datos = {}) {
        datos.funcion = funcion;
        
        $.ajax({
            url: 'Controller/controller.php',
            type: 'POST',
            data: datos,
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta del servidor:', response);
                
                if (response.success) {
                    showNotification('success', response.message || 'Operación exitosa');
                } else {
                    showNotification('error', response.message || 'Error en la operación');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', error);
                showNotification('error', 'Error al comunicarse con el servidor');
            }
        });
    }

    // Sistema de notificaciones (requiere librería como Toastr o SweetAlert)
    function showNotification(type, message) {
        // Si tienes Toastr instalado:
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } 
        // Si tienes SweetAlert2:
        else if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type === 'success' ? 'success' : 'error',
                title: type === 'success' ? '¡Éxito!' : 'Error',
                text: message,
                timer: 3000
            });
        } 
        // Fallback con alert nativo
        else {
            alert(message);
        }
    }

    // Ejemplo de event listener
    $('.btn-example').on('click', function() {
        const id = $(this).data('id');
        console.log('Botón clickeado, ID:', id);
        
        // Llamar al controlador
        // callController('metodoEjemplo', { id: id });
    });

    // Validación de formularios
    $('form.needs-validation').on('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });
});
