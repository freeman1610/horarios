function mostrarFormulario() {
    Swal.fire({
        title: 'Crear Pensum',
        html:
            `<div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="form-floating mt-3 mb-3">
                            <input autocomplete="off" id="txtCodigo" class="form-control" placeholder="Codigo">
                            <label for="txtCodigo">Codigo</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-floating mb-3">
                            <input autocomplete="off" id="txtMateria" class="form-control" placeholder="">
                            <label for="txtMateria">Materia</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-floating mb-3">
                            <select id="pnf" class="form-select">
                                ${generarSelectPNF(false, 0)}
                            </select>
                            <label for="pnf">PNF</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-floating mb-3">
                            <input autocomplete="off" id="txtTrayecto" type="number" class="form-control" placeholder="Trayecto">
                            <label for="txtTrayecto">Trayecto</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-floating mb-3">
                        <input autocomplete="off" id="txtTrimestre" type="number" class="form-control" placeholder="Trimestre">    
                        <label for="txtTrimestre">Trimestre</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-floating mb-3">
                        <input autocomplete="off" id="txtUc" type="number" class="form-control" placeholder="UC">
                        <label for="txtUc">UC (Unidades de Credito)</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-floating mb-3">
                        <input autocomplete="off" id="txtHoras" type="number" class="form-control" placeholder="Horas">
                        <label for="txtHoras">Horas</label>
                        </div>
                    </div>
                </div>
            </div>`,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Crear',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const codigo = Swal.getPopup().querySelector('#txtCodigo').value;
            const materia = Swal.getPopup().querySelector('#txtMateria').value;
            const PNF = Swal.getPopup().querySelector('#pnf').value;
            const trayecto = Swal.getPopup().querySelector('#txtTrayecto').value;
            const trimestre = Swal.getPopup().querySelector('#txtTrimestre').value;
            const uc = Swal.getPopup().querySelector('#txtUc').value;
            const horas = Swal.getPopup().querySelector('#txtHoras').value;

            if (!codigo || !materia || PNF == '' || !trayecto || !(trayecto <= 4 && trayecto >= 0) || !trimestre || !(trimestre < 4 && trimestre > 0) || !uc || !horas || !horas > 0) {
                Swal.showValidationMessage('Por favor, completa todos los campos');
                return false;
            }

            // Llamar a la función AJAX para crear el Pensum
            crearPensum(codigo, materia, PNF, trayecto, trimestre, uc, horas);
        }
    });

}
function generarSelectPNF(update, id) {

    $.ajax({
        url: 'crud_pensum.php',
        type: 'POST',
        data: {
            tipo: 'generarSelectPNF'
        },
        success: function (res) {
            let html = '<option value="">Seleccione</option>'
            let seleccionado = ''

            res.pnf.forEach(item => {
                if (update) {
                    seleccionado = item.id == id ? 'selected' : ''
                }

                html += `<option ${seleccionado} value="${item.id}">${item.name}</option>`
            });
            $("#pnf").html(html)
        },
        error: function (err) {
            console.log(err);
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


function crearPensum(codigo, materia, PNF, trayecto, trimestre, uc, horas) {
    $.ajax({
        url: 'crud_pensum.php',
        type: 'POST',
        data: {
            tipo: 'crear',
            codigo: codigo,
            materia: materia,
            pnf_id: PNF,
            trayecto: trayecto,
            trimestre: trimestre,
            uc: uc,
            horas: horas
        },
        success: function (response) {
            if (response.success) {
                Swal.fire('¡Pensum ' + materia + ' creado!', '', 'success');
                cargarPensum();
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
    cargarPensum(); // Cargar los Pensum al cargar la página
});

function asignarProfesiones(id, materia, trayecto, update, pnf) {
    $.ajax({
        url: 'crud_pensum.php',
        type: 'POST',
        data: {
            tipo: 'listar_asignar_profesion',
            id: id,
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
                title: '<div class="text-start">PNF: ' + pnf + '<br>MATERIA: ' + materia + '<br>TRAYECTO: ' + trayecto + '</div>',
                showConfirmButton: false,
                showCancelButton: false,
                showCloseButton: true,
                width: '1000px',
                html: `<div class="container">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-floating mb-3">
                                        <input autocomplete="off" type="text" id="searchInput" onkeyup="buscar2(this)" class="form-control" autocomplete="off" placeholder="Buscar">
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
        guardarPensumProfesion(datosGuardados, id)
    } else {
        return false
    }
}

function guardarPensumProfesion(arr, id_pensum) {
    $.ajax({
        url: 'crud_pensum.php',
        type: 'POST',
        data: {
            tipo: 'guardarPensumProfesion',
            id_pensum: id_pensum,
            datos: arr
        },
        success: function () {
            cargarPensum()
            Swal.fire({
                timer: 5000,
                timerProgressBar: true,
                position: 'bottom-start',
                toast: true,
                showConfirmButton: false,
                icon: 'success',
                title: 'Se ha asignado las Profesiones exitosamente',
            })
        },
        error: function (err) {
            console.log(err);
        }
    });
}

function cargarPensum() {
    // Realizar una solicitud AJAX para obtener los datos de los Pensum
    $.ajax({
        url: 'crud_pensum.php',
        type: 'POST',
        data: { tipo: 'listar' },
        success: function (response) {
            if (response.success) {
                var Pensum = response.Pensum;
                // Limpiar la tabla antes de agregar los datos
                $('#tablaPensum').empty();

                // Agregar los datos de los Pensum a la tabla
                $.each(Pensum, function (i, Pensum) {
                    var row = `<tr>
                        <td> ${Pensum.codigo} </td>
                        <td> ${Pensum.materia} </td>
                        <td> ${Pensum.name_pnf} </td>
                        <td> ${Pensum.trayecto} </td>
                        <td> ${Pensum.trimestre} </td>
                        <td> ${Pensum.uc} </td>
                        <td> ${Pensum.horas} </td>
                        ${Pensum.registro_profesion == 0 ? `<td>No Hay Profesiones Asociadas a este Pensum<br><button class="btn btn-success" onclick="asignarProfesiones(${Pensum.id}, '${Pensum.materia}', ${Pensum.trayecto}, false, '${Pensum.name_pnf}')">Crear</button></td>` : `<td><button class="btn btn-primary" onclick="asignarProfesiones(${Pensum.id}, '${Pensum.materia}', ${Pensum.trayecto}, true, '${Pensum.name_pnf}')">Actualizar</button></td>`}
                        '<td><button class="btn btn-primary" onclick="actualizarPensum(${Pensum.id})">Actualizar</button></td>' +
                        '<td><button class="btn btn-danger" onclick="eliminarPensum(${Pensum.id})">Eliminar</button></td>' +
                    '</tr>`;

                    $('#tablaPensum').append(row);
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Ha ocurrido un error al cargar los Pensum', 'error');
        }
    });
}

function actualizarPensum(id) {
    // Mostrar el formulario desplegable de actualización utilizando SweetAlert2

    $.ajax({
        url: 'crud_pensum.php',
        type: 'POST',
        data: {
            tipo: 'listar_uno',
            id: id
        },
        success: function (res) {
            Swal.fire({
                title: 'Actualizar Pensum',
                html:
                    `<div class="container">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-floating mt-3 mb-3">
                                    <input autocomplete="off" value="${res.pensum.codigo}" id="txtCodigo" class="form-control" placeholder="Codigo">
                                    <label for="txtCodigo">Codigo</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <input autocomplete="off" value="${res.pensum.materia}" id="txtMateria" class="form-control" placeholder="">
                                    <label for="txtMateria">Materia</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <select id="pnf" class="form-select">
                                        ${generarSelectPNF(true, res.pensum.pnf_id)}
                                    </select>
                                    <label for="pnf">PNF</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <input autocomplete="off" value="${res.pensum.trayecto}" id="txtTrayecto" type="number" class="form-control" placeholder="Trayecto">
                                    <label for="txtTrayecto">Trayecto</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <input autocomplete="off" value="${res.pensum.trimestre}" id="txtTrimestre" type="number" class="form-control" placeholder="Trimestre">    
                                    <label for="txtTrimestre">Trimestre</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <input autocomplete="off" value="${res.pensum.uc}" id="txtUc" type="number" class="form-control" placeholder="UC">
                                    <label for="txtUc">UC (Unidades de Credito)</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <input autocomplete="off" value="${res.pensum.horas}" id="txtHoras" type="number" class="form-control" placeholder="Horas">
                                    <label for="txtHoras">Horas</label>
                                </div>
                            </div>
                        </div>
                    </div>`,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Actualizar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const codigo = Swal.getPopup().querySelector('#txtCodigo').value;
                    const materia = Swal.getPopup().querySelector('#txtMateria').value;
                    const PNF = Swal.getPopup().querySelector('#pnf').value;
                    const trayecto = Swal.getPopup().querySelector('#txtTrayecto').value;
                    const trimestre = Swal.getPopup().querySelector('#txtTrimestre').value;
                    const uc = Swal.getPopup().querySelector('#txtUc').value;
                    const horas = Swal.getPopup().querySelector('#txtHoras').value;

                    if (!codigo || !materia || PNF == '' || !trayecto || !(trayecto <= 4 && trayecto >= 0) || !trimestre || !(trimestre < 4 && trimestre > 0) || !uc || !horas || !horas > 0) {
                        Swal.showValidationMessage('Por favor, completa todos los campos');
                        return false;
                    }

                    // Llamar a la función AJAX para actualizar el Pensum
                    actualizarPensumAjax(id, codigo, materia, PNF, trayecto, trimestre, uc, horas);

                }
            });
        }
    })


}

function actualizarPensumAjax(id, codigo, materia, PNF, trayecto, trimestre, uc, horas) {
    // Realizar una solicitud AJAX para actualizar el Pensum en la base de datos
    $.ajax({
        url: 'crud_pensum.php',
        type: 'POST',
        data: {
            tipo: 'actualizar',
            id: id,
            codigo: codigo,
            materia: materia,
            pnf_id: PNF,
            trayecto: trayecto,
            trimestre: trimestre,
            uc: uc,
            horas: horas
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
                    title: '¡Pensum ' + codigo + ' actualizado!',
                })
                // Recargar la tabla de Pensum para reflejar los cambios
                cargarPensum();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Ha ocurrido un error al procesar la solicitud', 'error');
        }
    });
}

function eliminarPensum(id) {
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
            // Llamar a la función AJAX para eliminar el Pensum
            eliminarPensumAjax(id);
        }
    });
}

function eliminarPensumAjax(id) {
    // Realizar una solicitud AJAX para eliminar el Pensum de la base de datos
    $.ajax({
        url: 'crud_pensum.php',
        type: 'POST',
        data: {
            id: id,
            tipo: 'eliminar'
        },
        success: function () {
            if (response.success) {
                Swal.fire({
                    timer: 5000,
                    timerProgressBar: true,
                    position: 'bottom-start',
                    toast: true,
                    showConfirmButton: false,
                    icon: 'success',
                    title: '¡Pensum eliminado!',
                })

                // Recargar la tabla de Pensum para reflejar los cambios
                cargarPensum();
            }
        },
        error: function () {
            Swal.fire('Error', 'Ha ocurrido un error al procesar la solicitud', 'error');
        }
    });
}