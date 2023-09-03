
function buscar(botn) {
    var value = $(botn).val().toLowerCase();
    $("#dataTable tbody tr").filter(function () {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
}

$(document).ready(function () {
    cargarPensum(); // Cargar los Pensum al cargar la página
});

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
                                    ${Pensum.registro_profesion == 0 ? `<td>No Hay Profesiones Asociadas a este Pensum</td>` : `<td><button class="btn btn-primary" onclick="asignarProfesiones(${Pensum.id}, '${Pensum.materia}', ${Pensum.trayecto}, true, '${Pensum.name_pnf}')">Ver Profesiones</button></td>`}
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
            let contentTabla = ''

            if (update) {
                res.profesiones_asignadas.forEach(item => {
                    contentTabla += `<li>COD: ${item.id} | ${item.name}</li>`
                });
            }

            Swal.fire({
                title: '<div class="text-start">PNF: ' + pnf + '<br>MATERIA: ' + materia + '<br>TRAYECTO: ' + trayecto + '</div>',
                showConfirmButton: false,
                showCancelButton: false,
                showCloseButton: true,
                width: '1000px',
                html: `<div class="container">
                            <div class="row">
                                <div class="col-12" >
                                    <h3 class="text-start">Profesiones</h3>
                                    <div id="tabla22" class="table text-start" style="max-height: 300px; overflow: auto; width: 900px;" id="rightTable">
                                        ${update ? '<ul id="rightList">' + contentTabla + '</ul>' : '<h4 id="h3TablaRight" class="h4 text-warning">' + contentTabla + '</h4><ul id="rightList"></ul>'}
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