

function cargarSelectSede() {
    $.ajax({
        url: 'estudiante.php',
        method: "POST",
        data: {
            tipo: 'cargarSelectSede'
        },
        success: function (res) {
            if (res.status == 1) {
                let option = ''
                res.datos.forEach(function (item) {
                    if (item.name == 'CENTRAL') {
                        option += '<option value="' + item.id + '">SAN CRISTOBAL</option>'

                    } else {
                        option += '<option value="' + item.id + '">' + item.name + '</option>'

                    }
                })
                $('#selectSede').html($('#selectSede').html() + option)
                return true
            }
            if (res.status == 0) {
                Swal.fire({
                    timer: 5000,
                    timerProgressBar: true,
                    position: 'bottom-start',
                    toast: true,
                    showConfirmButton: false,
                    icon: 'info',
                    title: 'No hay Sedes disponibles'
                })
                return true
            }
        },
        error: function (error) {
            console.error(error);
        }
    });
}

function cargarSecciones(sede) {
    if (sede.value == '') {
        $('#selecSeccion').html('<option value="">Seleccione</option>')
        $('#selectSemana').html('<option value="">Seleccione</option>')
        $('#selectTurno').val('')

        $('#selecSeccion').attr('disabled', true)
        $('#selectTurno').attr('disabled', true)
        $('#selectSemana').attr('disabled', true)
        $('#tabla1').addClass('d-none')
        $('#tabla2').addClass('d-none')
        $('#titleTableMateria').addClass('d-none')
        return false
    }
    $("#selecSeccion").val('')
    $("#selectTurno").val('')
    $("#selectSemana").val('')

    $.ajax({
        url: 'estudiante.php',
        method: "POST",
        data: {
            tipo: 'cargarSelectSeccion',
            sede: sede.value
        },
        success: function (res) {
            if (res.status == 0) {
                // Obtener el índice de la opción seleccionada
                let selectedIndex = sede.selectedIndex;
                // Obtener el texto de la opción seleccionada usando el índice
                let selectedOptionText = sede.options[selectedIndex].text;
                Swal.fire({
                    timer: 5000,
                    timerProgressBar: true,
                    position: 'bottom-start',
                    toast: true,
                    showConfirmButton: false,
                    icon: 'info',
                    title: 'No hay Secciones disponibles en la Sede de ' + selectedOptionText
                })
                $('#selecSeccion').html('<option value="">Seleccione</option>')
                $('#selecSeccion').attr('disabled', true)
                $('#selectTurno').attr('disabled', true)
                $('#selectSemana').attr('disabled', true)
                $('#tabla1').addClass('d-none')
                $('#tabla2').addClass('d-none')
                $('#titleTableMateria').addClass('d-none')

                return true
            }
            let option = ''
            res.datos.forEach(function (item) {
                option += '<option value="' + item.id + '">' + item.pnf + ' | ' + item.trayecto + item.seccion + '</option>'
            })
            $('#selecSeccion').html($('#selecSeccion').html() + option)
            $('#selecSeccion').removeAttr('disabled')
            $('#selectTurno').removeAttr('disabled')
            return true
        },
        error: function (error) {
            console.error(error);
        }
    });
}
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
function cargarTds(res) {


    let html1 = ''
    let html2 = ''
    let hora = 1

    res.materias.forEach(item => {
        html1 = ''
        for (let j = 0; j < 6; j++) {
            switch (j) {
                case 0:
                    html1 += '<td style="background-color: ' + coloresSuaves[hora] + ';" class="text-white" id="materia' + item.codigo + '">' + item.codigo + '</td>'
                    break;
                case 1:
                    html1 += '<td style="background: ' + coloresSuaves[hora] + ';" class="text-white">' + item.materia + '</td>'
                    break;
                case 2:

                    var matchingData = res.profesores.find(function (item2) {
                        return item2.codigo === item.codigo;
                    })

                    if (matchingData) {

                        html1 += '<td class="text-white bg-secondary">' + matchingData.name + ' ' + matchingData.last_name + '</td>'
                    } else {
                        html1 += '<td class="text-white bg-danger">Sin Asignar</td>'
                    }
                    break;
                case 3:
                    html1 += '<td class="text-white bg-secondary">' + item.uc + '</td>'
                    break
                case 4:
                    html1 += '<td class="text-white bg-secondary">' + item.horas + '</td>'
                    break;
                case 5:
                    if (matchingData) {
                        html1 += '<td class="text-black"><span class="text-success">Inicio</span>: ' + matchingData.semana_inicio + ', <span class="text-danger">Fin</span>: ' + matchingData.semana_fin + '</td>'

                    } else {
                        html1 += '<td class="text-white bg-danger">Sin Asignar</td>'

                    }
                    break;

            }
        }
        html2 += `<tr>${html1}</tr>`

        hora++
    });
    $('#tbodyTablaMaterias1').html('')
    $('#tbodyTablaMaterias1').html(html2)

    html1 = ''
    html2 = ''
    hora = 0
    res.horas_dia.forEach(item => {
        html1 = ''
        for (let j = 0; j < 7; j++) {
            if (j == 0) {
                html1 += '<td class="text-white bg-success">' + item.contenido + '</td>'
            }
            html1 += '<td class="hour-button">Hora Libre</td>'
        }
        if (hora > 3) {
            html2 += `<tr class="tdDiurno">${html1}</tr>`
        } else {
            html2 += `<tr>${html1}</tr>`
        }
        hora++
    })
    for (let i = 0; i < 12; i++) {

    }
    $('#tbodyTablaMaterias2').html('')
    $('#tbodyTablaMaterias2').html(html2)

    var buttons = document.querySelectorAll('.hour-button');
    let i = 1

    buttons.forEach(function (button) {
        var matchingData = res.datos.find(function (item) {
            return item.hour === i;
        });
        button.classList.remove('bg-secondary')
        button.classList.remove('text-white')
        button.classList.remove('text-start')

        if (matchingData) {
            button.style.backgroundColor = $("#materia" + matchingData.pensum_codigo).css("background-color")
            button.classList.add('text-white')
            button.classList.add('text-start')
            button.innerText = matchingData.pensum_codigo + ' / Aula: ' + matchingData.aula_name

        } else {

            button.classList.add('bg-secondary')
            button.classList.add('text-white')
            button.classList.add('text-start')
            button.innerText = 'Hora Libre'
        }
        i++

    });
}
function mostrarContenido() {
    var tdDiurno = document.querySelectorAll(".tdDiurno");
    tdDiurno.forEach(function (td) {
        td.classList.remove('d-none')
    });
}

function ocultarContenido() {
    var tdDiurno = document.querySelectorAll(".tdDiurno");

    tdDiurno.forEach(function (td) {
        td.classList.add('d-none')
    });

}

function cargarSemanas() {
    $.ajax({
        url: 'estudiante.php',
        method: "POST",
        data: {
            tipo: 'cargarSemanas',
            seccion_id: $("#selecSeccion").val(),
            tipo_horario: $("#selectTurno").val()
        },
        success: function (res) {
            $('#selectSemana').removeAttr('disabled')
            let isSelected = $('#selectSemana').val()
            $('#selectSemana').html('')

            html = ''

            let estilos = ''

            for (let i = 0; i < 36; i++) {

                var matchingData = res.lista.find(function (item) {
                    return parseInt(item.semana) === i;
                });
                if (matchingData) {
                    estilos = 'style="background:' + coloresSuaves[matchingData.conjunto] + '; color:#fff"'
                } else {
                    estilos = ''
                }

                let seleccionado = i == isSelected ? 'selected' : ''

                if (i == 0) {
                    html += '<option value="">Seleccione</option>';
                } else {
                    html += '<option value="' + i + '" ' + estilos + ' ' + seleccionado + '>' + i + '</option>';
                }
            }
            $('#selectSemana').html(html)
        },
        error: function (error) {
            console.error(error);
        }
    });
}

function cargarContenido() {

    if ($('#selectSede').val() != '' && $('#selecSeccion').val() != '' && $('#selectTurno').val() != '' && $('#selectSemana').val() == '') {
        cargarSemanas()
        return true
    }

    if ($('#selectSede').val() != '' && $('#selecSeccion').val() != '' && $('#selectTurno').val() != '' && $('#selectSemana').val() != '') {
        cargarSemanas()
        $.ajax({
            url: 'estudiante.php',
            method: "POST",
            data: {
                tipo: 'cargarHorario',
                seccion_id: $('#selecSeccion').val(),
                tipo_horario: $('#selectTurno').val(),
                semana: $('#selectSemana').val()
            },
            success: function (res) {

                cargarTds(res)

                $('#tabla1').removeClass('d-none')
                $('#tabla2').removeClass('d-none')
                $('#titleTableMateria').removeClass('d-none')

                $('#titleTableMateria').text('Horario de la Sección ' + res.titleH3[0].trayecto + res.titleH3[0].seccion + ' del PNF ' + res.titleH3[0].pnf_name)

                if ($('#selectTurno').val() == "diurno") {
                    mostrarContenido()
                } else if ($('#selectTurno').val() === "nocturno") {
                    ocultarContenido()
                }
            },
            error: function (error) {
                console.error(error);
            }
        });
    }
}

document.addEventListener("DOMContentLoaded", function () {
    $('#selecSeccion').val('')
    $('#selectTurno').val('')
    $('#selectSemana').val('')
    cargarSelectSede()
});

function cargarHorario() {
    if (!$('#selectSede').val() && !$('#selecSeccion').val() && !$('#selectTurno').val() && !$('#selectSemana').val()) {
        return false
    }
}