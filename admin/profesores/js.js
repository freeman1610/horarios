function mostrarFormulario() {
    Swal.fire({
        title: 'Crear Profesor',
        html:
            '<div class="form-floating mb-3"><input id="txtNombre" class="form-control" autocomplete="off" placeholder="Nombre"><label for="txtNombre">Nombre</label></div>' +
            '<div class="form-floating mb-3"><input id="txtApellido" autocomplete="off" class="form-control" placeholder="Apellido"><label for="txtApellido">Apellido</label></div>' +
            '<div class="form-floating mb-3"><input id="txtCedula" autocomplete="off" type="number" class="form-control" placeholder="Cedula"><label for="txtCedula">Cedula</label></div>' +
            '<select id="sedeID" class="form-select">' +
            cargarSelectSede(false, 0) +
            '</select>',
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Crear',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const nombre = Swal.getPopup().querySelector('#txtNombre').value;
            const apellido = Swal.getPopup().querySelector('#txtApellido').value;
            const cedula = Swal.getPopup().querySelector('#txtCedula').value;
            const sede_id = Swal.getPopup().querySelector('#sedeID').value;


            if (!nombre || !apellido || !cedula || sede_id == '') {
                Swal.showValidationMessage('Por favor, completa todos los campos');
                return false;
            }

            // Llamar a la función AJAX para crear el profesor
            crearProfesor(nombre, apellido, cedula, sede_id);
        }
    });

}

function buscar(botn) {
    var value = $(botn).val().toLowerCase();
    $("#dataTable tbody tr").filter(function () {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
}

function buscar2(botn) {
    var value = $(botn).val().toLowerCase();
    $("#tabla22 ul li").filter(function () {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
}


function cargarSelectSede(update, id) {
    $.ajax({
        url: 'crud_profesor.php',
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
function crearProfesor(nombre, apellido, cedula, sede_id) {
    $.ajax({
        url: 'crud_profesor.php',
        type: 'POST',
        data: {
            tipo: 'crear',
            name: nombre,
            last_name: apellido,
            cedula: cedula,
            sede_id: sede_id
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
                    title: '¡Profesor ' + apellido + ' ' + nombre + ' creado/a!'
                })
                cargarProfesores();
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
    cargarProfesores(); // Cargar los profesores al cargar la página
});


function ordenarLista(lista) {
    var lis = lista.children('li');
    lis.sort(function (a, b) {
        var aId = $(a).data('id');
        var bId = $(b).data('id');
        var aText = $(a).text();
        var bText = $(b).text();
        if (aId === bId) {
            return aText.localeCompare(bText);
        } else {
            return aId - bId;
        }
    });
    lista.empty().append(lis);
}

function verificarListaVacia(lista) {
    if (lista.children().length === 0) {
        $('#h3TablaRight').removeClass('d-none')
        $('#guardarButton').addClass('d-none')
    } else {
        $('#h3TablaRight').addClass('d-none')
        $('#guardarButton').removeClass('d-none')
    }
}

// Mover elementos de izquierda a derecha
function leftList(id, name, lii) {
    $('#guardarButton').removeClass('d-none')
    $('#h3TablaRight').addClass('d-none')
    $(lii).remove();
    $('#rightList').append(`<li onclick="rightList(${id}, '${name}', this)" data-id="${id}">COD: ${id} | ${name}</li>`);
    ordenarLista($('#rightList'));
    verificarListaVacia($('#rightList'))
}

// Mover elementos de derecha a izquierda
function rightList(id, name, lii) {
    $('#guardarButton').removeClass('d-none')
    $('#h3TablaRight').addClass('d-none')
    $(lii).remove();
    $('#leftList').append(`<li onclick="leftList(${id}, '${name}', this)" data-id="${id}">COD: ${id} | ${name}</li>`);
    ordenarLista($('#leftList'))
    verificarListaVacia($('#rightList'))
}

// Guardar los datos
function guardarButton(id) {
    let datosGuardados = [];
    $('#rightList li').each(function () {
        datosGuardados.push({ id: $(this).data('id') });
    });
    if (datosGuardados.length > 0) {
        guardarPerfilProfesional(datosGuardados, id)
    } else {
        return false
    }
}

function guardarPerfilProfesional(arr, profesor_id) {
    $.ajax({
        url: 'crud_profesor.php',
        type: 'POST',
        data: {
            tipo: 'guardarPerfilProfesional',
            profesor_id: profesor_id,
            datos: arr
        },
        success: function () {
            cargarProfesores()
            Swal.fire({
                timer: 5000,
                timerProgressBar: true,
                position: 'bottom-start',
                toast: true,
                showConfirmButton: false,
                icon: 'success',
                title: 'Se ha guardado el Perfil Profesional exitosamente'
            })
        },
        error: function (err) {
            console.log(err);
        }
    });
}


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
            let contentTablaLeft = ''
            let contentTablaRight = ''

            if (update) {
                res.profesiones.forEach(item => {
                    contentTablaLeft += `<li onclick="leftList(${item.id}, '${item.name}', this)" data-id="${item.id}">COD: ${item.id} | ${item.name}</li>`
                });
                res.profesiones_asignadas.forEach(item => {
                    contentTablaRight += `<li onclick="rightList(${item.id}, '${item.name}', this)" data-id="${item.id}" data-id="${item.id}">COD: ${item.id} | ${item.name}</li>`
                });
            } else {
                res.profesiones.forEach(item => {
                    contentTablaLeft += `<li onclick="leftList(${item.id}, '${item.name}', this)" data-id="${item.id}">COD: ${item.id} | ${item.name}</li>`
                });
                contentTablaRight = 'No hay registros'
            }

            Swal.fire({
                title: '<div class="text-start">Profesor: ' + apellido_nombre + '<br>Cedula: <span class="user-select-all">' + cedula + '</span> | Sede: ' + sede,
                showConfirmButton: false,
                showCancelButton: false,
                showCloseButton: true,
                width: '1000px',
                html: `<div class="container">
                            <div class="row">
                                <div class="col-md-6 col-12">
                                <div class="form-floating mb-3">
                                    <input type="text" id="searchInput" onkeyup="buscar2(this)" class="form-control" autocomplete="off"
                                        placeholder="Buscar">
                                    <label for="searchInput">Buscar</label>
                                </div>
                                </div>
                            </div>
                            <div class="row" style="width:900px;">
                                <div class="col-6">
                                    <h3 class="text-start">Profesiones Disponibles</h3>
                                    <div id="tabla22" class="table text-start" style="max-height: 300px; overflow: auto;" id="leftTable">
                                        <ul id="leftList">
                                        ${contentTablaLeft}
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h3 class="text-start">Profesiones Asignadas</h3>
                                    <div class="table text-start" style="max-height: 300px; overflow: auto;" id="rightTable">
                                        ${update ? '<ul id="rightList">' + contentTablaRight + '</ul>' : '<h4 id="h3TablaRight" class="h4 text-warning">' + contentTablaRight + '</h4><ul id="rightList"></ul>'}
                                    </div>
                                </div>
                            </div>
                            <div class="row d-flex justify-content-center" style="width:900px;">
                                <div class="col-12 d-grid gap-2">
                                    <button class="btn btn-success d-none" id="guardarButton" onclick="guardarButton(${id})">Guardar</button>
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
                        <td>${profesor.total_perfiles == 0 ? `No se ha creado un Perfil<br><button class="btn btn-success" onclick="sweetCrearPerfilProfesional(${profesor.id},'${profesor.last_name} ${profesor.name}',false,'${profesor.sede_name}',${profesor.cedula})">Crear</button>` : `<button class="btn btn-primary" onclick="sweetCrearPerfilProfesional(${profesor.id},'${profesor.last_name} ${profesor.name}',true,'${profesor.sede_name}',${profesor.cedula})">Actualizar</button>`}</td>
                        <td>${html}<br><a class="btn btn-secondary mt-1" href="disponibilidad/disponibilidad.php?id=${profesor.id}">Ver</a></td>
                        <td><button class="btn btn-primary" onclick="actualizarProfesor(${profesor.id})">Actualizar</button></td>
                        <td><button class="btn btn-danger" onclick="eliminarProfesor(${profesor.id})">Eliminar</button></td>
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

function actualizarProfesor(id) {
    // Mostrar el formulario desplegable de actualización utilizando SweetAlert2

    $.ajax({
        url: 'crud_profesor.php',
        type: 'POST',
        data: {
            tipo: 'listar_uno',
            id: id
        },
        success: function (res) {
            Swal.fire({
                title: 'Actualizar Profesor',
                html:
                    '<div class="form-floating mb-3"><input value="' + res.profesor.name + '" id="txtNombre" class="form-control" autocomplete="off" placeholder="Nombre"><label for="txtNombre">Nombre</label></div>' +
                    '<div class="form-floating mb-3"><input value="' + res.profesor.last_name + '" id="txtApellido" autocomplete="off" class="form-control" placeholder="Apellido"><label for="txtApellido">Apellido</label></div>' +
                    '<div class="form-floating mb-3"><input value="' + res.profesor.cedula + '" id="txtCedula" autocomplete="off" type="number" class="form-control" placeholder="Cedula"><label for="txtCedula">Cedula</label></div>' +
                    '<select id="sedeID" class="form-select">' +
                    cargarSelectSede(true, res.profesor.sede_id) +
                    '</select>',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Actualizar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const nombre = Swal.getPopup().querySelector('#txtNombre').value;
                    const apellido = Swal.getPopup().querySelector('#txtApellido').value;
                    const cedula = Swal.getPopup().querySelector('#txtCedula').value;
                    const sede_id = Swal.getPopup().querySelector('#sedeID').value;


                    if (!nombre || !apellido || !cedula || sede_id == '') {
                        Swal.showValidationMessage('Por favor, completa todos los campos');
                        return false;
                    }

                    // Llamar a la función AJAX para actualizar el profesor
                    actualizarProfesorAjax(id, nombre, apellido, cedula, sede_id);

                }
            });
        }
    })


}

function actualizarProfesorAjax(id, nombre, apellido, cedula, sede_id) {
    // Realizar una solicitud AJAX para actualizar el profesor en la base de datos
    $.ajax({
        url: 'crud_profesor.php',
        type: 'POST',
        data: {
            tipo: 'actualizar',
            id: id,
            name: nombre,
            last_name: apellido,
            cedula: cedula,
            sede_id: sede_id
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
                    title: '¡Profesor ' + nombre + ' ' + apellido + ' actualizado!'
                })
                // Recargar la tabla de profesores para reflejar los cambios
                cargarProfesores();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Ha ocurrido un error al procesar la solicitud', 'error');
        }
    });
}

function eliminarProfesor(id) {
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
            // Llamar a la función AJAX para eliminar el profesor
            eliminarProfesorAjax(id);
        }
    });
}

function eliminarProfesorAjax(id) {
    // Realizar una solicitud AJAX para eliminar el profesor de la base de datos
    $.ajax({
        url: 'crud_profesor.php',
        type: 'POST',
        data: {
            id: id,
            tipo: 'eliminar'
        },
        success: function (response) {
            if (response.success) {
                Swal.fire('¡Profesor eliminado!', '', 'success');

                // Recargar la tabla de profesores para reflejar los cambios
                cargarProfesores();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Ha ocurrido un error al procesar la solicitud', 'error');
        }
    });
}