$(document).ready(function () {
    $.ajax({
        url: '../profesor_disponibilidad/crud_profesor_disponibilidad.php',
        method: "POST",
        data: { tipo: 'mostrar_name' },
        success: function (res) {
            if (res.status == 0) {
                $('#divSave').html('<button id="btnGuardar" onclick="guardarDatos()" class="btn btn-success">Guardar</button>')
                $('#h3Status').html('Estado: <span class="text-danger">Sin Crear</span>')
                $('#statuss').val(0)
            }
            if (res.status == 1) {
                $('#h3Status').html('Estado: <span class="text-success">Guardado</span>')
                $('#divSave').html('')
                $('#divSave').addClass('d-none')
                $('#statuss').val(1)
            }

            if (res.status == 2) {
                $('#h3Status').html('Estado: <span class="text-warning">Sin Cargar</span>')
                $('#divSave').html('<button id="btnGuardar" onclick="guardarDatos()" class="btn btn-success">Guardar</button>')
                $('#statuss').val(2)
            }
            $('#nombreProfesor').html('Disponibilidad: </br>' + res.datos_profe.name + ' ' + res.datos_profe.last_name);
        },
        error: function (error) {
            console.error(error);
        }
    });
});

function mouseHoverr(datos) {
    var dataTD = JSON.parse(datos.dataset.coleccion);
    if (dataTD.disponibilidad != 2) {
        return true;
    }
    datos.innerHTML = 'Hora Asignada ' + dataTD.htmll
}
function mouseLeavee(datos) {
    var dataTD = JSON.parse(datos.dataset.coleccion);
    if (dataTD.disponibilidad != 2) {
        return true;
    }
    datos.innerHTML = 'Hora Asignada, Ver Más'
}

function cargarTabla() {
    // Obtener la URL actual
    var urlActual = window.location.href;

    // Crear un objeto URLSearchParams con la URL de la página obtenida
    var params = new URLSearchParams(new URL(urlActual).search);

    html1 = ''
    html2 = ''
    hora = 0

    $.ajax({
        type: "POST",
        url: "../profesor_disponibilidad/crud_profesor_disponibilidad.php",
        data: {
            tipo: 'cargar_horas_dia',
            tipo_h: $('#tipoHorario').val()
        },
        success: function (res) {
            res.horas_dia.forEach(item => {
                if (hora > 3) {
                    html2 += `<tr class="tdDiurno">
                                <td class="bg-success text-white">${item.contenido}</td>
                                <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                                    onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{}'>
                                </td>
                                <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                                    onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{}'>
                                </td>
                                <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                                    onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{}'>
                                </td>
                                <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                                    onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{}'>
                                </td>
                                <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                                    onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{}'>
                                </td>
                                <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                                    onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{}'>
                                </td>
                                <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                                    onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{}'>
                                </td>
                            </tr>`
                } else {
                    html2 += `<tr>
                    <td class="bg-success text-white">${item.contenido}</td>
                    <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                        onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{}'>
                    </td>
                    <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                        onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{}'>
                    </td>
                    <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                        onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{}'>
                    </td>
                    <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                        onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{}'>
                    </td>
                    <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                        onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{}'>
                    </td>
                    <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                        onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{}'>
                    </td>
                    <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                        onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{}'>
                    </td>
                </tr>`
                }
                hora++

            })
            $('#tbodyTablaMaterias2').html('')
            $('#tbodyTablaMaterias2').html(html2)
        }
    });
    $('#tbodyTablaMaterias2').html('')
    $('#tbodyTablaMaterias2').html(html2)

    const coloresSuaves = [
        '#f44336',
        '#e91e63',
        '#9c27b0',
        '#673ab7',
        '#3f51b5',
        '#2196f3',
        '#03a9f4',
        '#00bcd4',
        '#009688',
        '#4caf50',
        '#8bc34a',
        '#cddc39',
        '#ffeb3b',
        '#ffc107',
        '#ff9800',
        '#ff5722',
        '#795548',
        '#9e9e9e',
        '#607d8b',
        '#e53935',
        '#d81b60',
        '#8e24aa',
        '#5e35b1',
        '#3949ab',
        '#1e88e5',
        '#039be5',
        '#00acc1',
        '#00897b',
        '#43a047',
        '#7cb342',
        '#c0ca33',
        '#fdd835',
        '#ffb300',
        '#fb8c00',
        '#f4511e'
    ];
    // Obtener el valor del parámetro 'id'
    var id = params.get('id');
    $.ajax({
        url: '../profesor_disponibilidad/crud_profesor_disponibilidad.php',
        method: "POST",
        data: {
            id: id,
            tipo: 'cargar_tabla',
            horario: $('#tipoHorario').val()
        },
        success: function (res) {

            var buttons = document.querySelectorAll('.hour-button');
            let i = 1
            buttons.forEach(function (button) {
                button.classList.remove('bg-primary')
                button.classList.remove('bg-secondary')
                button.classList.remove('bg-danger');

                if ($('#statuss').val() == 1) {
                    button.classList.remove('cursor-td-pointer')
                    button.classList.add('cursor-td-default')
                }

                var coleccion = JSON.parse(button.dataset.coleccion);
                var matchingData1 = res.data_profesor_disponibilidad.find(function (item) {
                    return item.hour === i;
                });
                if (matchingData1) {
                    coleccion.posicion = matchingData1.hour
                    if (matchingData1.disponibilidad) {
                        var matchingData2 = res.data_hora.filter(function (item) {
                            return item.posicion === i;
                        });

                        if (matchingData2.length > 0) {
                            var buttonContent = '';
                            let o = 0
                            matchingData2.forEach(function (data) {
                                buttonContent += '<div class="p-2 mt-2" style="background:' + coloresSuaves[o] + '"> Carrera: ' + data.coordinacion +
                                    '</br>Sección: ' + data.trayecto_seccion + data.seccion_seccion +
                                    '</br>Pensum: ' + data.pensum_name +
                                    '</br>Aula: ' + data.aula_name +
                                    '</br>Semana Inicio: ' + data.semana_inicio +
                                    '</br>Semana Fin: ' + data.semana_fin + '</br></div>'
                                o++
                            });

                            button.classList.add('bg-danger');
                            button.classList.add('text-white');
                            button.innerHTML = 'Hora Asignada, Ver Más';
                            coleccion.disponibilidad = 2;
                            coleccion.htmll = buttonContent
                            button.dataset.coleccion = JSON.stringify(coleccion);
                        } else {
                            button.classList.add('bg-primary');
                            button.classList.add('text-white');
                            button.innerText = 'Disponible';
                            coleccion.disponibilidad = 1;
                            button.dataset.coleccion = JSON.stringify(coleccion);
                        }
                    } else {
                        coleccion.posicion = i
                        coleccion.disponibilidad = 0
                        button.classList.add('bg-secondary')
                        button.classList.add('text-white')
                        button.dataset.coleccion = JSON.stringify(coleccion);
                        button.innerText = 'Ocupado'
                    }



                } else {
                    coleccion.posicion = i
                    coleccion.disponibilidad = 0
                    button.classList.add('bg-secondary')
                    button.classList.add('text-white')
                    button.dataset.coleccion = JSON.stringify(coleccion);
                    button.innerText = 'Ocupado'
                }
                i++
            });
        },
        error: function (error) {
            console.error(error);
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("tipoHorario").value = ''
});



function mostrarTitulo() {
    var select = document.getElementById("tipoHorario");
    var selectedValue = select.options[select.selectedIndex].value;

    if (selectedValue === "diurno") {
        cargarTabla()
        mostrarContenido()
    } else if (selectedValue === "nocturno") {
        cargarTabla()
        ocultarContenido()
    }
    $('#tableMain').removeClass('d-none')
}

function guardarDatos() {
    Swal.fire({
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        icon: 'warning',
        title: '¿Estas seguro de Guardar la Carga Horaria?',
        text: 'Una vez guarda no podra modificarse hasta que un Coordinador lo vuelva a desbloquear',
        preConfirm: () => {
            $.ajax({
                url: '../profesor_disponibilidad/crud_profesor_disponibilidad.php',
                method: "POST",
                data: { tipo: 'guardar_carga_horaria' },
                success: function (res) {
                    res.status == 1 ? $('#statuss').val(1) : ''
                    $('#h3Status').html('Estado: <span class="text-success">Guardado</span>')
                    $('#divSave').html('')
                    $('#divSave').addClass('d-none')
                    cargarTabla()
                    Swal.fire({
                        timer: 5000,
                        timerProgressBar: true,
                        position: 'bottom-start',
                        toast: true,
                        showConfirmButton: false,
                        icon: 'success',
                        title: 'Carga Horaria guardada'
                    })
                },
                error: function (error) {
                    console.error(error);
                }
            });
        }
    })
}


function cambiarEstado(td) {

    if ($('#statuss').val() == 1) {
        return true
    }

    var datos = JSON.parse(td.getAttribute("data-coleccion"));

    if (datos.disponibilidad === 2) {
        Swal.fire({
            timer: 5000,
            timerProgressBar: true,
            position: 'bottom-start',
            toast: true,
            showConfirmButton: false,
            icon: 'info',
            title: 'No puedes cambiar una hora ya Asignada por un Coordinador (Codigo: 2)'
        })
        return true
    }

    let tipoHorario = document.getElementById("tipoHorario").value

    if (datos.disponibilidad == 0) {
        td.textContent = "Disponible";
        datos.disponibilidad = 1;
        datos.tipoh = tipoHorario
        td.classList.remove('bg-secondary')
        td.classList.add('bg-primary')
    } else if (datos.disponibilidad == 1) {
        td.textContent = "Ocupado";
        datos.disponibilidad = 0;
        datos.tipoh = tipoHorario
        td.classList.remove('bg-primary')
        td.classList.add('bg-secondary')
    }

    $.ajax({
        url: '../profesor_disponibilidad/crud_profesor_disponibilidad.php',
        method: "POST",
        data: { tipo: 'verificar', datos: datos },
        success: function () {

        },
        error: function (error) {
            console.error(error);
        }
    });
    td.setAttribute("data-coleccion", JSON.stringify(datos));
}

var contenidoGuardado = null;

function ocultarContenido() {
    var tdDiurno = document.querySelectorAll(".tdDiurno");

    tdDiurno.forEach(function (td) {
        td.classList.add('d-none')
    });

}

function mostrarContenido() {
    var tdDiurno = document.querySelectorAll(".tdDiurno");

    tdDiurno.forEach(function (td) {
        td.classList.remove('d-none')
    });
}


