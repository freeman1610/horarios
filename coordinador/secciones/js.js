
function mostrarFormulario() {
    Swal.fire({
        title: 'Crear Seccion',
        html:
            '<div class="form-floating mb-3"><input id="Trayecto" autocomplete="off" type="number" class="form-control" placeholder=""><label for="Trayecto">Trayecto</label></div>' +
            '<div class="form-floating mb-3"><input type="text" id="seccionName" autocomplete="off" class="form-control" placeholder="Apellido"><label for="seccionName">Sección</label></div>',
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Crear',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {

            if ($('#Trayecto').val() == '' || $('#seccionName').val() == '') {
                Swal.showValidationMessage('Por favor, completa todos los campos');
                return false;
            }
            if (!($('#Trayecto').val() >= 0 && $('#Trayecto').val() <= 4)) {
                $('#Trayecto').addClass('is-invalid');
                Swal.showValidationMessage('El Trayecto debe de estar en el rango de 0 al 4');
                return false;
            }

            // Llamar a la función AJAX para crear el Seccion
            crearSeccion($('#Trayecto').val(), $('#seccionName').val(), $('#pnfID option:selected').text());
        }
    });
}

function crearSeccion(Trayecto, seccion, pnfName) {
    $.ajax({
        url: 'crud_seccion.php',
        type: 'POST',
        data: {
            tipo: 'crear',
            trayecto: Trayecto,
            seccion: seccion
        },
        success: function (response) {
            if (response.success) {
                Swal.fire({
                    timer: 5000,
                    timerProgressBar: true,
                    position: 'bottom-start',
                    toast: true,
                    showConfirmButton: false,
                    icon: 'success',
                    title: '¡Seccion ' + pnfName + ' Trayecto ' + Trayecto + ' ' + seccion + ' creado!'
                })
                cargarSeccion();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Ha ocurrido un error al procesar la solicitud', 'error');
        }
    });
}



$(document).ready(function () {
    cargarSeccion(); // Cargar los Seccion al cargar la página
});

function cargarSeccion() {
    // Realizar una solicitud AJAX para obtener los datos de los Seccion
    $.ajax({
        url: 'crud_seccion.php',
        type: 'POST',
        data: { tipo: 'listar' },
        success: function (response) {
            if (response.success) {
                if (response.Seccion.length == 0) {
                    let html = '<tr><td colspan="6">No hay registros</td></tr>'
                    $('#tablaSeccion').append(html);
                    return false
                }
                // Limpiar la tabla antes de agregar los datos
                $('#tablaSeccion').empty();

                // Agregar los datos de los Seccion a la tabla
                var row = ''
                $.each(response.Seccion, function (i, Seccion) {
                    row += `<tr>
                            <td> ${Seccion.trayecto} </td>
                            <td> ${Seccion.seccion} </td>
                            <td><button class="btn btn-primary" onclick="actualizarSeccion( ${Seccion.id} )">Actualizar</button></td>
                            <td>
                                ${Seccion.horario_asignados > 0 ? '<span class="text-success">Sección con Horario Creado</span>' : `<button class="btn btn-danger" onclick="eliminarSeccion( ${Seccion.id} )">Eliminar</button>`}
                            </td>
                        </tr>`

                });
                $('#tablaSeccion').append(row);

            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Ha ocurrido un error al cargar los Seccion', 'error');
        }
    });
}

function actualizarSeccion(id) {
    // Mostrar el formulario desplegable de actualización utilizando SweetAlert2

    $.ajax({
        url: 'crud_seccion.php',
        type: 'POST',
        data: {
            tipo: 'listar_uno',
            id: id
        },
        success: function (res) {
            Swal.fire({
                title: 'Actualizar Seccion',
                html:
                    '<div class="form-floating mb-3"><input value="' + res.Seccion.trayecto + '" id="Trayecto" autocomplete="off" type="number" class="form-control" placeholder=""><label for="Trayecto">Trayecto</label></div>' +
                    '<div class="form-floating mb-3"><input value="' + res.Seccion.seccion + '" type="text" id="seccionName" autocomplete="off" class="form-control" placeholder="Apellido"><label for="seccionName">Sección</label></div>',

                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Actualizar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {

                    if ($('#Trayecto').val() == '' || $('#seccionName').val() == '') {
                        Swal.showValidationMessage('Por favor, completa todos los campos');
                        return false;
                    }
                    if (!($('#Trayecto').val() >= 0 && $('#Trayecto').val() <= 4)) {
                        $('#Trayecto').addClass('is-invalid');
                        Swal.showValidationMessage('El Trayecto debe de estar en el rango de 0 al 4');
                        return false;
                    }
                    // Llamar a la función AJAX para actualizar el Seccion
                    actualizarSeccionAjax(id, $('#Trayecto').val(), $('#seccionName').val(), $('#pnfID option:selected').text());

                }
            });
        }
    })


}

function actualizarSeccionAjax(id, Trayecto, seccion, pnfName) {
    // Realizar una solicitud AJAX para actualizar el Seccion en la base de datos
    $.ajax({
        url: 'crud_seccion.php',
        type: 'POST',
        data: {
            tipo: 'actualizar',
            id: id,
            trayecto: Trayecto,
            seccion: seccion
        },
        success: function (response) {
            if (response.success) {
                Swal.fire({
                    timer: 5000,
                    timerProgressBar: true,
                    position: 'bottom-start',
                    toast: true,
                    showConfirmButton: false,
                    icon: 'success',
                    title: '¡Seccion ' + pnfName + ' ' + Trayecto + ' ' + seccion + ' actualizado!'
                })
                // Recargar la tabla de Seccion para reflejar los cambios
                cargarSeccion();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Ha ocurrido un error al procesar la solicitud', 'error');
        }
    });
}

function eliminarSeccion(id) {
    // Mostrar la confirmación de eliminación utilizando SweetAlert2
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Llamar a la función AJAX para eliminar el Seccion
            eliminarSeccionAjax(id);
        }
    });
}

function eliminarSeccionAjax(id) {
    // Realizar una solicitud AJAX para eliminar el Seccion de la base de datos
    $.ajax({
        url: 'crud_seccion.php',
        type: 'POST',
        data: {
            id: id,
            tipo: 'eliminar'
        },
        success: function (response) {
            if (response.success) {
                Swal.fire({
                    timer: 5000,
                    timerProgressBar: true,
                    position: 'bottom-start',
                    toast: true,
                    showConfirmButton: false,
                    icon: 'success',
                    title: '¡Seccion eliminado!'
                })
                // Recargar la tabla de Seccion para reflejar los cambios
                cargarSeccion();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Ha ocurrido un error al procesar la solicitud', 'error');
        }
    });
}