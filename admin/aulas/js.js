function cargarSelectSede(update, id) {
    $.ajax({
        url: 'crud_aula.php',
        type: 'POST',
        data: {
            tipo: 'listar_sedes',
        },
        success: function (res) {
            let option = '<option value="">Seleccione</option>'
            let seleccionado = ''
            res.sedes.forEach(function (item) {
                if (update) {
                    seleccionado = item.id == id ? 'selected' : ''
                }
                option += '<option ' + seleccionado + ' value="' + item.id + '">' + item.name + '</option>'
            })
            $('#sedeID').html(option)
            return true
        },
        error: function (err) {
            console.log(err);
        }
    });
}

function mostrarFormulario() {
    Swal.fire({
        title: 'Crear Aula',
        html: `<div class="form-floating mb-3">
                <input id="txtNombre" autocomplete="off" type="text" class="form-control" placeholder="">
                <label for="txtNombre">Nombre</label>
            </div>
            <div class="form-floating mb-3">
                <input id="txtCodigo" autocomplete="off" type="text" class="form-control" placeholder="">
                <label for="txtCodigo">Codigo</label>
            </div>
            <div class="form-floating mb-3">
                <input id="txtCapacidad" autocomplete="off" type="number" class="form-control" placeholder="">
                <label for="txtCapacidad">Capacidad</label>
            </div>
            <div class="form-floating mb-3">
                <select id="sedeID" class="form-select">
                    ${cargarSelectSede(false, 0)}
                </select>    
                <label for="sedeID">Sede</label>
            </div>
            `,

        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Crear',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            if (!$('#txtNombre').val() || !$('#txtCodigo').val() || !$('#txtCapacidad').val() || !$('#sedeID').val()) {
                Swal.showValidationMessage('Por favor, completa todos los campos');
                return false;
            }

            // Llamar a la función AJAX para crear el Aula
            crearAula($('#txtNombre').val(), $('#txtCodigo').val(), $('#txtCapacidad').val(), $('#sedeID').val());
        }
    });
}

function crearAula(Nombre, codigo, Capacidad, sede_id) {
    $.ajax({
        url: 'crud_aula.php',
        type: 'POST',
        data: {
            tipo: 'crear',
            name: Nombre,
            codigo: codigo,
            capacity: Capacidad,
            sede_id: sede_id
        },
        success: function (response) {
            if (response.success) {
                Swal.fire('¡Aula ' + Nombre + ', Capacidad: ' + Capacidad + ' creado!', '', 'success');
                cargarAula();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Ha ocurrido un error al procesar la solicitud', 'error');
        }
    });
}

function buscar(botn) {
    var value = $(botn).val().toLowerCase();
    $("#dataTable tbody tr").filter(function () {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
}

$(document).ready(function () {
    cargarAula(); // Cargar los Aula al cargar la página
});

function cargarAula() {
    // Realizar una solicitud AJAX para obtener los datos de los Aula
    $.ajax({
        url: 'crud_aula.php',
        type: 'POST',
        data: { tipo: 'listar' },
        success: function (response) {
            if (response.success) {
                if (response.Aula.length == 0) {
                    $('#tablaAula').append('<tr><td colspan="6">No hay registros</td></tr>')
                    return true
                }
                var Aula = response.Aula;
                // Limpiar la tabla antes de agregar los datos
                $('#tablaAula').empty();

                // Agregar los datos de los Aula a la tabla
                $.each(Aula, function (i, Aula) {
                    var row = '<tr>' +
                        '<td>' + Aula.sede_name + '</td>' +
                        '<td>' + Aula.codigo + '</td>' +
                        '<td>' + Aula.name + '</td>' +
                        '<td>' + Aula.capacity + '</td>' +
                        '<td><button class="btn btn-primary" onclick="actualizarAula(' + Aula.id + ')">Actualizar</button></td>' +
                        '<td><button class="btn btn-danger" onclick="eliminarAula(' + Aula.id + ')">Eliminar</button></td>' +
                        '</tr>';

                    $('#tablaAula').append(row);
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Ha ocurrido un error al cargar los Aula', 'error');
        }
    });
}

function actualizarAula(id) {
    // Mostrar el formulario desplegable de actualización utilizando SweetAlert2

    $.ajax({
        url: 'crud_aula.php',
        type: 'POST',
        data: {
            tipo: 'listar_uno',
            id: id
        },
        success: function (res) {
            Swal.fire({
                title: 'Actualizar Aula',
                html: `<div class="form-floating mb-3">
                        <input id="txtNombre" value="${res.Aula.name}" autocomplete="off" type="text" class="form-control" placeholder="">
                        <label for="txtNombre">Nombre</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input id="txtCodigo" value="${res.Aula.codigo}" autocomplete="off" type="text" class="form-control" placeholder="">
                        <label for="txtCodigo">Codigo</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input id="txtCapacidad" value="${res.Aula.capacity}" autocomplete="off" type="number" class="form-control" placeholder="">
                        <label for="txtCapacidad">Capacidad</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select id="sedeID" class="form-select">
                            ${cargarSelectSede(true, res.Aula.sede_id)}
                        </select>
                        <label for="sedeID">Sede</label>
                    </div>`,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Actualizar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    if (!$('#txtNombre').val() || !$('#txtCodigo').val() || !$('#txtCapacidad').val() || !$('#sedeID').val()) {
                        Swal.showValidationMessage('Por favor, completa todos los campos');
                        return false;
                    }

                    // Llamar a la función AJAX para actualizar el Aula
                    actualizarAulaAjax(id, $('#txtNombre').val(), $('#txtCodigo').val(), $('#txtCapacidad').val(), $('#sedeID').val());

                }
            });
        }
    })


}

function actualizarAulaAjax(id, Nombre, codigo, Capacidad, sede_id) {
    // Realizar una solicitud AJAX para actualizar el Aula en la base de datos
    $.ajax({
        url: 'crud_aula.php',
        type: 'POST',
        data: {
            tipo: 'actualizar',
            id: id,
            name: Nombre,
            capacity: Capacidad,
            codigo: codigo,
            sede_id: sede_id
        },
        success: function (response) {
            if (response.success) {
                Swal.fire('¡Aula ' + codigo + ' actualizado!', '', 'success');

                // Recargar la tabla de Aula para reflejar los cambios
                cargarAula();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Ha ocurrido un error al procesar la solicitud', 'error');
        }
    });
}

function eliminarAula(id) {
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
            // Llamar a la función AJAX para eliminar el Aula
            eliminarAulaAjax(id);
        }
    });
}

function eliminarAulaAjax(id) {
    // Realizar una solicitud AJAX para eliminar el Aula de la base de datos
    $.ajax({
        url: 'crud_aula.php',
        type: 'POST',
        data: {
            id: id,
            tipo: 'eliminar'
        },
        success: function (response) {
            if (response.success) {
                Swal.fire('¡Aula eliminado!', '', 'success');

                // Recargar la tabla de Aula para reflejar los cambios
                cargarAula();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Ha ocurrido un error al procesar la solicitud', 'error');
        }
    });
}