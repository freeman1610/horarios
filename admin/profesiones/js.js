function profesores(id_profesion, profesion_name) {
    $.ajax({
        url: 'crud_profesion.php',
        method: "POST",
        data: {
            tipo: 'listar_profesores',
            id_profesion: id_profesion
        },
        success: function (res) {
            let html1 = ''
            res.profesores.forEach(item => {
                html1 += `<tr>
                            <td>${item.cedula}</td>
                            <td>${item.name} ${item.last_name}</td>
                        </tr>`
            });
            Swal.fire({
                title: 'Profesores de la Profesion: ' + profesion_name + '<br><a class="fs-6 link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover" href="../profesores/" target="_blank">Agregar Profesores</a>',
                html: `<table class="table" style="width: 900px;">
                            <thead>
                                <tr>
                                    <th>Cedula</th>
                                    <th>Nombre y Apellido</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${html1}
                            </tbody>
                        </table>`,
                showConfirmButton: false,
                showCancelButton: false,
                width: '1000px',
                showCloseButton: true
            })
        },
        error: function (error) {
            console.error(error);
        }
    });
}
function pensums(id_profesion, profesion_name) {
    $.ajax({
        url: 'crud_profesion.php',
        method: "POST",
        data: {
            tipo: 'listar_pensums',
            id_profesion: id_profesion
        },
        success: function (res) {
            let html1 = ''
            res.pensums.forEach(item => {
                html1 += `<tr>
                            <td>${item.pnf_name}</td>
                            <td>${item.codigo}</td>
                            <td>${item.materia}</td>
                            <td>${item.trayecto}</td>
                        </tr>`
            });
            Swal.fire({
                title: 'Pensums de la Profesion: ' + profesion_name + '<br><a class="fs-6 link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover" href="../pensum/" target="_blank">Agregar Pensum</a>',
                html: `<table class="table" style="width: 900px;">
                            <thead>
                                <tr>
                                    <th>PNF</th>
                                    <th>Codigo</th>
                                    <th>Nombre</th>
                                    <th>Trayecto</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${html1}
                            </tbody>
                        </table>`,
                showConfirmButton: false,
                showCancelButton: false,
                width: '1000px',
                showCloseButton: true
            })
        },
        error: function (error) {
            console.error(error);
        }
    });
}

function crear(name, codigo) {
    $.ajax({
        type: "POST",
        url: "crud_profesion.php",
        data: {
            tipo: 'crear',
            name: name,
            codigo: codigo
        },
        success: function () {
            Swal.fire({
                timer: 5000,
                timerProgressBar: true,
                position: 'bottom-start',
                toast: true,
                showConfirmButton: false,
                icon: 'success',
                title: 'Profesion Creada'
            })
            listarProfesiones()
        },
        error: function (error) {
            console.error(error);
        }
    });
}

function mostrarFormulario() {
    $.ajax({
        type: "POST",
        url: "crud_profesion.php",
        data: {
            tipo: 'mostrar_codigos_disponibles'
        },
        success: function (res) {
            Swal.fire({
                title: 'Crear Profesión',
                html: `<span>Codigos Disponibles:<br>TSU: <span class="text-success">${res.codigos_disponibles[0].ultimo_codigo + 1}</span> | INGENIERO: <span class="text-success">${res.codigos_disponibles[1].ultimo_codigo + 1}</span> | LICENCIADO: <span class="text-success">${res.codigos_disponibles[2].ultimo_codigo + 1}</span></span>
                        <div class="form-floating mt-3 mb-3">
                            <input id="txtCodigo" autocomplete="off" type="number" class="form-control" placeholder="">
                            <label for="txtCodigo">Codigo</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input id="txtNombre" autocomplete="off" type="text" class="form-control" placeholder="">
                            <label for="txtNombre">Nombre</label>
                        </div>`,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Crear',
                cancelButtonText: 'Cancelar',
                showCloseButton: true,
                preConfirm: () => {
                    if (!$('#txtNombre').val() || !$('#txtCodigo').val()) {
                        Swal.showValidationMessage('Por favor, completa todos los campos');
                        return false;
                    }

                    // Llamar a la función AJAX
                    crear($('#txtNombre').val(), $('#txtCodigo').val());

                }
            })
        }
    });

}

function actualizarAjax(id_profesion, name, codigo) {
    $.ajax({
        type: "POST",
        url: "crud_profesion.php",
        data: {
            tipo: 'actualizar_ajax',
            id_profesion: id_profesion,
            name: name,
            codigo: codigo
        },
        success: function () {
            Swal.fire({
                timer: 5000,
                timerProgressBar: true,
                position: 'bottom-start',
                toast: true,
                showConfirmButton: false,
                icon: 'success',
                title: 'Profesion Actualizada'
            })
            listarProfesiones()
        },
        error: function (error) {
            console.error(error);
        }
    });
}

function actualizar(id_profesion, profesion_name) {
    $.ajax({
        url: 'crud_profesion.php',
        method: "POST",
        data: {
            tipo: 'form_actualizar',
            id_profesion: id_profesion
        },
        success: function (res) {
            Swal.fire({
                title: 'Actualizar:<br>' + profesion_name,
                html: `<div class="form-floating mb-3">
                            <input id="txtCodigo" value="${res.profesion[0].codigo}" autocomplete="off" type="number" class="form-control" placeholder="">
                            <label for="txtCodigo">Codigo</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input id="txtNombre" value="${res.profesion[0].name}" autocomplete="off" type="text" class="form-control" placeholder="">
                            <label for="txtNombre">Nombre</label>
                        </div>`,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Actualizar',
                cancelButtonText: 'Cancelar',
                showCloseButton: true,
                preConfirm: () => {
                    if (!$('#txtNombre').val() || !$('#txtCodigo').val()) {
                        Swal.showValidationMessage('Por favor, completa todos los campos');
                        return false;
                    }

                    // Llamar a la función AJAX
                    actualizarAjax(id_profesion, $('#txtNombre').val(), $('#txtCodigo').val())

                }
            })
        },
        error: function (error) {
            console.error(error);
        }
    });
}

function eliminarProfesionAjax(id_profesion) {
    $.ajax({
        type: "POST",
        url: "crud_profesion.php",
        data: {
            tipo: 'eliminar',
            id_profesion: id_profesion
        },
        success: function () {
            Swal.fire({
                timer: 5000,
                timerProgressBar: true,
                position: 'bottom-start',
                toast: true,
                showConfirmButton: false,
                icon: 'success',
                title: 'Profesion Eliminada'
            })
            listarProfesiones()
        },
        error: function (error) {
            console.error(error);
        }
    });
}

function eliminar(id) {
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
            // Llamar a la función AJAX
            eliminarProfesionAjax(id);
        }
    });
}

function listarProfesiones() {
    $.ajax({
        url: 'crud_profesion.php',
        method: "POST",
        data: {
            tipo: 'listarProfesiones'
        },
        success: function (res) {
            let html = ''
            let html1 = ''
            res.profesiones.forEach(item => {
                html1 = `<td><button class="btn btn-success" onclick="actualizar(${item.id}, '${item.name}')">Actualizar</button></td>
                <td><button class="btn btn-danger" onclick="eliminar(${item.id}, '${item.name}')">Eliminar</button></td>`
                html += `<tr>
                            <td>${item.id}</td>
                            <td>${item.name}</td>
                            <td>${item.total_profesores == 0 ? 'No hay Profesores con esta Profesión' : item.total_profesores + `<br><button class="mt-1 btn btn-sm btn-secondary" onclick="profesores(${item.id}, '${item.name}')">Ver Profesores</button>`}</td>
                            <td>${item.total_pensum == 0 ? 'No hay Materias con esta Profesión' : item.total_pensum + `<br><button class="mt-1 btn btn-sm btn-secondary" onclick="pensums(${item.id}, '${item.name}')">Ver Pensums</button>`}</td>
                            ${item.total_profesores == 0 && item.total_pensum == 0 ? html1 : `<td><button class="btn btn-success" onclick="actualizar(${item.id}, '${item.name}')">Actualizar</button></td>`}
                        </tr>`
            });
            $('#tablaProfesion').html(html)
        },
        error: function (error) {
            console.error(error);
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
    listarProfesiones(); // Cargar los Pensum al cargar la página
});