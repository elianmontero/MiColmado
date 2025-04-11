// JavaScript para pasar datos al modal
 document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const nombre = button.getAttribute('data-nombre');
        const email = button.getAttribute('data-email');
        
        const modalId = editModal.querySelector('#edit_id');
        const modalNombre = editModal.querySelector('#edit_nombre');
        const modalEmail = editModal.querySelector('#edit_email');
        
        modalId.value = id;
        modalNombre.value = nombre;
        modalEmail.value = email;
    });
});

    //Alerta para confirmar el delete en la DB
    function confirmDelete(id) { 
    Swal.fire({
        title: '¿Estas seguro?',
        text: "¡Este usuario se eliminará permanentemente!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Aceptar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirigir a la URL de eliminación
            window.location.href = 'index.php?delete=' + id;
        }
    });
}

$(document).ready(function() {
    $('#userTable').DataTable({
        "language": {
            "paginate": {
                "previous": "Anterior",
                "next": "Siguiente"
            },
            "search": "Buscar:",
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "No se encontraron resultados",
            "info": "Mostrando página _PAGE_ de _PAGES_",
            "infoEmpty": "No hay registros disponibles",
            "infoFiltered": "(filtrado de _MAX_ registros totales)"
        }
    });
});
