

function buscar(botn) {
    var value = $(botn).val().toLowerCase();
    $("#dataTable tbody tr").filter(function () {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
}


$(document).ready(function () {
    cargarProfesores(); // Cargar los profesores al cargar la página
});


function sweetCrearPerfilProfesional(id, apellido_nombre, update, sede, cedula) {
    $.ajax({
        url: 'crud_profesor.php',
        type: 'POST',
        data: {
            tipo: 'listar_asignar_profesion',
            id_profesor: id,
            update: update
        },
        success: function (res) {
            let contentTabla = ''

            if (update) {
                res.profesiones_asignadas.forEach(item => {
                    contentTabla += `<li>COD: ${item.id} | ${item.name}</li>`
                });
            }

            Swal.fire({
                title: '<div class="text-start">Profesor: ' + apellido_nombre + '<br>Cedula: <span class="user-select-all">' + cedula + '</span> | Sede: ' + sede,
                showConfirmButton: false,
                showCancelButton: false,
                showCloseButton: true,
                width: '1000px',
                html: `<div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="text-start">Profesiones</h3>
                                    <div class="table text-start" style="max-height: 300px; overflow: auto;" id="rightTable">
                                        <ul id="rightList">${contentTabla}</ul>
                                    </div>
                                </div>
                            </div>
                        </div>`
            })
        },
        error: function () {
            Swal.fire('Error', 'Ha ocurrido un error al procesar la solicitud', 'error');
        }
    });
}


function cargarProfesores() {
    // Realizar una solicitud AJAX para obtener los datos de los profesores
    $.ajax({
        url: 'crud_profesor.php',
        type: 'POST',
        data: { tipo: 'listar' },
        success: function (response) {
            if (response.success) {
                var profesores = response.profesores;

                // Limpiar la tabla antes de agregar los datos
                $('#tablaProfesores').empty();
                let html = ''
                // Agregar los datos de los profesores a la tabla
                $.each(profesores, function (index, profesor) {
                    if (profesor.disponibilidad_status_creada == 1) {
                        if (profesor.disponibilidad_status) {
                            html = '<span class="text-success">Guardado</span>'
                        } else {
                            html = '<span class="text-warning">Sin Guardar</span>'
                        }
                    }
                    if (profesor.disponibilidad_status_creada == 0) {
                        html = '<span class="text-danger">Sin crear</span>'
                    }
                    var row = `<tr>
                                    <td>${profesor.last_name}</td>
                                    <td>${profesor.name}</td>
                                    <td>${profesor.cedula}</td>
                                    <td>${profesor.sede_name}</td>
                                    <td>${profesor.total_perfiles == 0 ? `No se ha creado un Perfil` : `<button class="btn btn-primary" onclick="sweetCrearPerfilProfesional(${profesor.id},'${profesor.last_name} ${profesor.name}',true,'${profesor.sede_name}',${profesor.cedula})">Ver</button>`}</td>
                                    <td>${html}<br><a class="btn btn-secondary mt-1" href="disponibilidad/disponibilidad.php?id=${profesor.id}">Ver</a></td>
                                </tr>`;

                    $('#tablaProfesores').append(row);
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Ha ocurrido un error al cargar los profesores', 'error');
        }
    });
}