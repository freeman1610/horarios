

function mouseHoverr(datos) {
    var dataTD = JSON.parse(datos.dataset.coleccion);

    if (!dataTD.update) {
        return true;
    }
    var content = `
                Profesor: ${dataTD.profesor_name} ${dataTD.profesor_last_name} </br>
                Aula: ${dataTD.aula_name} | Capacidad: ${dataTD.aula_capacity} </br>
                Materia: ${dataTD.pensum_name} </br>
                Semana Incio: ${dataTD.semana_inicio}</br>
                Semana Fin: ${dataTD.semana_fin}         
        `;

    datos.innerHTML = content
}

function borrar(id) {
    Swal.fire({
        toast: true,
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        icon: 'warning',
        title: '¿Estas seguro de Eliminar esta Hora?',
        preConfirm: () => {
            $.ajax({
                url: 'crud_horario.php',
                method: "POST",
                data: {
                    tipo: 'borrar',
                    id: id
                },
                success: function () {
                    cargarContenido()
                    cargarSemanas()
                    Swal.fire({
                        timer: 5000,
                        timerProgressBar: true,
                        position: 'bottom-start',
                        toast: true,
                        showConfirmButton: false,
                        icon: 'success',
                        title: '¡Hora Borrada!'
                    })
                },
                error: function (error) {
                    console.error(error);
                }
            });
        }
    })
}


function mouseLeavee(datos) {
    var dataTD = JSON.parse(datos.dataset.coleccion);
    if (!dataTD.update) {
        return true;
    }
    datos.innerText = 'Hora Asignada, Ver Más'
}

function cargarSelectSeccion() {
    $.ajax({
        url: 'crud_horario.php',
        method: "POST",
        data: {
            tipo: 'cargarSelectSeccion'
        },
        success: function (res) {
            res.datos.forEach(function (item) {
                let option = '<option value="' + item.id + '">' + item.pnf + ' | ' + item.trayecto + item.seccion + '</option>'
                let sec = document.getElementById('selectSeccion')
                sec.innerHTML = sec.innerHTML + option
            })
        },
        error: function (error) {
            console.error(error);
        }
    });
}

function cargarSecciones(sede) {
    if (sede.value == '') {
        $('#selectSemana').html('<option value="">Seleccione</option>')
        $('#selectTipoH').val('')

        document.getElementById('selectSeccion').setAttribute('disabled', 'true')
        document.getElementById('selectTipoH').setAttribute('disabled', 'true')
        document.getElementById('selectSemana').setAttribute('disabled', 'true')
        document.getElementById('tablaHorario').classList.add('d-none')
        return false
    }
    document.getElementById("selectSeccion").value = ''
    document.getElementById("selectTipoH").value = ''
    document.getElementById("selectSemana").value = ''

    $.ajax({
        url: 'crud_horario.php',
        method: "POST",
        data: {
            tipo: 'cargarSelectSeccion'
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
                document.getElementById('selectSeccion').innerHTML = '<option value="">Seleccione</option>'
                document.getElementById('selectSeccion').setAttribute('disabled', 'true')
                document.getElementById('selectTipoH').setAttribute('disabled', 'true')
                document.getElementById('selectSemana').setAttribute('disabled', 'true')
                document.getElementById('tablaHorario').classList.add('d-none')
                return true
            }
            res.datos.forEach(function (item) {
                let option = '<option value="' + item.id + '">' + item.pnf + ' | ' + item.trayecto + item.seccion + '</option>'
                let sec = document.getElementById('selectSeccion')
                sec.innerHTML = sec.innerHTML + option
            })
            document.getElementById('selectSeccion').removeAttribute('disabled')
            document.getElementById('selectTipoH').removeAttribute('disabled')

        },
        error: function (error) {
            console.error(error);
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("selectSeccion").value = ''
    document.getElementById("selectTipoH").value = ''
    document.getElementById('selectSemana').value = ''
    cargarSelectSeccion()
});

var dragSrcElement = null;

function handleDragStart(event) {
    dragSrcElement = this;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/html', this.innerHTML);
    this.classList.add('dragging');
}

function handleDragOver(event) {
    if (event.preventDefault) {
        event.preventDefault();
    }
    event.dataTransfer.dropEffect = 'move';
    return false;
}

function handleDrop(event) {
    if (event.stopPropagation) {
        event.stopPropagation();
    }
    if (dragSrcElement !== this) {
        var dragData = JSON.parse(dragSrcElement.dataset.coleccion);
        var dropData = JSON.parse(this.dataset.coleccion);

        let horaTDdragData = $(dragSrcElement).closest("tr").find("td:first").text()
        let horaTDdropData = $(this).closest("tr").find("td:first").text()


        if (!dragData.update && !dropData.update) {
            return true
        }

        $.ajax({
            url: 'crud_horario.php',
            type: 'POST',
            data: {
                tipo: 'drag',
                data_origen: dragData,
                data_destino: dropData,
                tipo_horario: document.getElementById('selectTipoH').value
            },
            success: function (res) {
                if (res.success == true) {
                    Swal.fire({
                        timer: 5000,
                        timerProgressBar: true,
                        position: 'bottom-start',
                        toast: true,
                        showConfirmButton: false,
                        icon: 'success',
                        title: '¡Horario actualizado!',
                    })
                    cargarContenido()
                }
                if (res.status) {
                    switch (res.status) {
                        case 2:
                            Swal.fire({
                                timer: 5000,
                                timerProgressBar: true,
                                position: 'bottom-start',
                                toast: true,
                                showConfirmButton: false,
                                icon: 'info',
                                title: 'El Profesor ' + dragData.profesor_name + ' ' + dragData.profesor_last_name + ' no esta disponible para el dia ' + dropData.dia + ' a las ' + horaTDdropData,
                            })
                            break;
                        case 3:
                            Swal.fire({
                                timer: 5000,
                                timerProgressBar: true,
                                position: 'bottom-start',
                                toast: true,
                                showConfirmButton: false,
                                icon: 'info',
                                title: 'El Profesor ' + dragData.profesor_name + ' ' + dragData.profesor_last_name + ' ya esta asiganado ha esta hora en otra Sección: ' + res.datos[0].pnf + ' ' + res.datos[0].trayecto + res.datos[0].seccion
                            })
                            break;
                        case 4:
                            Swal.fire({
                                timer: 5000,
                                timerProgressBar: true,
                                position: 'bottom-start',
                                toast: true,
                                showConfirmButton: false,
                                icon: 'info',
                                title: 'El Aula ' + dragData.aula_name + ' | Capacidad: ' + dragData.aula_capacity + ' ya esta asiganado ha esta hora en otra Sección: ' + res.datos[0].pnf + ' ' + res.datos[0].trayecto + res.datos[0].seccion
                            })
                            break;
                        case 5:
                            Swal.fire({
                                timer: 5000,
                                timerProgressBar: true,
                                position: 'bottom-start',
                                toast: true,
                                showConfirmButton: false,
                                icon: 'info',
                                title: 'El Profesor ' + dropData.profesor_name + ' ' + dropData.profesor_last_name + ' no esta disponible para el dia ' + dragData.dia + ' a las ' + horaTDdragData,
                            })
                            break;
                        case 6:
                            Swal.fire({
                                timer: 5000,
                                timerProgressBar: true,
                                position: 'bottom-start',
                                toast: true,
                                showConfirmButton: false,
                                icon: 'info',
                                title: 'El Profesor ' + dropData.profesor_name + ' ' + dropData.profesor_last_name + ' ya esta asiganado ha esta hora en otra Sección: ' + res.datos[0].pnf + ' ' + res.datos[0].trayecto + res.datos[0].seccion
                            })
                            break;
                        case 7:
                            Swal.fire({
                                timer: 5000,
                                timerProgressBar: true,
                                position: 'bottom-start',
                                toast: true,
                                showConfirmButton: false,
                                icon: 'info',
                                title: 'El Aula ' + dropData.aula_name + ' | Capacidad: ' + dropData.aula_capacity + ', ya esta asiganado ha esta hora en otra Sección: ' + res.datos[0].pnf + ' ' + res.datos[0].trayecto + res.datos[0].seccion
                            })
                            break;
                    }
                }
            },
            error: function (err) {
                console.log(err);
            }
        });

    }
    return false;
}

function cargarBotones(res) {

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
                        let semanassss = '';
                        let semanasAgregadas = {}; // Usaremos un objeto para realizar un seguimiento de las semanas agregadas

                        res.profesores.forEach(item3 => {
                            if (matchingData.codigo == item3.codigo) {
                                const semanaInicio = item3.semana_inicio;
                                const semanaFin = item3.semana_fin;

                                // Verificar si la semana ya ha sido agregada
                                if (!semanasAgregadas[`${semanaInicio}-${semanaFin}`]) {
                                    semanassss += `<span class="text-success">Inicio</span>: ${semanaInicio}, <span class="text-danger">Fin</span>: ${semanaFin}<br>`;

                                    // Marcar la semana como agregada en el objeto
                                    semanasAgregadas[`${semanaInicio}-${semanaFin}`] = true;
                                }
                            }
                        });
                        html1 += '<td class="text-black">' + semanassss + '</td>';
                    } else {
                        html1 += '<td class="text-white bg-danger">Sin Asignar</td>';
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
        if (hora > 3) {
            html2 += `<tr class="tdDiurno">
                        <td data-id="${item.id}" class="bg-success text-white">${item.contenido}</td>
                        <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                            onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{"dia":"Lunes"}'>
                        </td>
                        <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                            onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{"dia":"Martes"}'>
                        </td>
                        <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                            onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{"dia":"Miercoles"}'>
                        </td>
                        <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                            onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{"dia":"Jueves"}'>
                        </td>
                        <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                            onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{"dia":"Viernes"}'>
                        </td>
                        <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                            onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{"dia":"Sabado"}'>
                        </td>
                        <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                            onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{"dia":"Domingo"}'>
                        </td>
                    </tr>`
        } else {
            html2 += `<tr>
                        <td data-id="${item.id}" class="bg-success text-white">${item.contenido}</td>
                        <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                            onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{"dia":"Lunes"}'>
                        </td>
                        <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                            onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{"dia":"Martes"}'>
                        </td>
                        <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                            onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{"dia":"Miercoles"}'>
                        </td>
                        <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                            onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{"dia":"Jueves"}'>
                        </td>
                        <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                            onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{"dia":"Viernes"}'>
                        </td>
                        <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                            onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{"dia":"Sabado"}'>
                        </td>
                        <td onclick="cambiarEstado(this)" class="hour-button" onmouseover="mouseHoverr(this)"
                            onmouseleave="mouseLeavee(this)" draggable="true" data-coleccion='{"dia":"Domingo"}'>
                        </td>
                    </tr>`
        }
        hora++
    })
    $('#tbodyTablaMaterias2').html('')
    $('#tbodyTablaMaterias2').html(html2)
    $('#h3Horario').removeClass('d-none')
    $('#h3Horario').text('Horario de la Semana ' + $('#selectSemana').val())

    var buttons = document.querySelectorAll('.hour-button');
    let i = 1

    buttons.forEach(function (button) {
        var coleccion = JSON.parse(button.dataset.coleccion);
        var matchingData = res.datos.find(function (item) {
            return item.hour === i;
        });
        button.classList.remove('bg-primary')
        button.classList.remove('bg-secondary')
        button.classList.remove('text-white')
        button.classList.remove('text-start')

        delete coleccion.horario_id
        delete coleccion.posicion
        delete coleccion.profesor
        delete coleccion.aula
        delete coleccion.pensum
        delete coleccion.update
        delete coleccion.profesor_name
        delete coleccion.profesor_last_name
        delete coleccion.aula_name
        delete coleccion.aula_capacity
        delete coleccion.pensum_name
        delete coleccion.semana_inicio
        delete coleccion.semana_fin

        button.dataset.coleccion = JSON.stringify(coleccion);

        if (matchingData) {
            coleccion.horario_id = matchingData.horario_id
            coleccion.posicion = matchingData.hour
            coleccion.profesor = matchingData.profesor_id
            coleccion.aula = matchingData.aula_id
            coleccion.aula_name = matchingData.aula_name
            coleccion.aula_capacity = matchingData.aula_capacity
            coleccion.pensum = matchingData.pensum_id
            coleccion.update = true
            coleccion.profesor_name = matchingData.nombre_profesor
            coleccion.profesor_last_name = matchingData.apellido_profesor
            coleccion.pensum_name = matchingData.pensum_name
            coleccion.semana_inicio = matchingData.semana_inicio
            coleccion.semana_fin = matchingData.semana_fin

            button.classList.add('bg-primary')
            button.classList.add('text-white')
            button.classList.add('text-start')
            button.dataset.coleccion = JSON.stringify(coleccion);
            button.innerText = 'Hora Asignada, Ver Más'

        } else {
            coleccion.posicion = i
            coleccion.update = false
            button.classList.add('bg-secondary')
            button.classList.add('text-white')
            button.classList.add('text-start')
            button.dataset.coleccion = JSON.stringify(coleccion);
            button.innerText = 'Hora Libre'
        }
        i++

    });
    buttons.forEach(function (button) {
        button.addEventListener('dragstart', handleDragStart, false);
        button.addEventListener('dragover', handleDragOver, false);
        button.addEventListener('drop', handleDrop, false);
        button.addEventListener('dragend', function () {
            this.classList.remove('dragging');
        });
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
        url: 'crud_horario.php',
        method: "POST",
        data: {
            tipo: 'cargarSemanas',
            seccion_id: $("#selectSeccion").val(),
            tipo_horario: $("#selectTipoH").val()
        },
        success: function (res) {
            $('#selectSemana').removeAttr('disabled')
            let isSelected = $('#selectSemana').val()
            $('#selectSemana').html('')

            html = ''

            let conjuntoAnterior = 1
            let estilos = ''

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

function desbloquearDispoProfe(id, texto, titulo) {
    Swal.fire({
        toast: true,
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        icon: 'warning',
        title: '¿Estas seguro de Desbloquear la Disponibilidad Horaria de este Profesor?',
        preConfirm: () => {
            $.ajax({
                url: 'crud_horario.php',
                method: "POST",
                data: {
                    tipo: 'desbloquearDispoProfe',
                    id: id
                },
                success: function () {
                    cargarContenido()
                    Swal.fire({
                        timer: 5000,
                        timerProgressBar: true,
                        position: 'bottom-start',
                        toast: true,
                        showConfirmButton: false,
                        icon: 'success',
                        title: '¡Profesor Desbloqueado!',
                    })
                },
                error: function (error) {
                    console.error(error);
                }
            });
        }
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.cancel) {
            verDisponibilidadProfe(texto, titulo)
        }
    });

}
function buscar(botn) {
    var value = $(botn).val().toLowerCase();
    $("#dataTable tbody tr").filter(function () {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
}
function verDisponibilidadProfe(texto, titulo) {
    $.ajax({
        url: 'crud_horario.php',
        method: "POST",
        data: {
            tipo: 'verDisponibilidadProfe',
            boton: texto
        },
        success: function (res) {
            let contenido = ''
            res.profesores.forEach(item => {
                btnVerDisponibilidad = `<td>
                                 <button onclick="window.open('../profesores/disponibilidad/disponibilidad.php?id=${item.id}', '_blank')" class="btn btn-small btn-secondary">Ver Disponibilidad Horaria</button>
                            </td>`
                onClickk = `onclick="window.open('../profesores/disponibilidad/disponibilidad.php?id=${item.id}', '_blank')"`
                if (item.status == 'sinCrear') {
                    contenido += `<tr>
                                    <td style="width: 100px;">${item.cedula}</td>
                                    <td>${item.name} ${item.last_name}</td>
                                    <td class="text-white bg-danger">El Profesor No ha Creado su Disponibilidad Horaria</td>
                                    <td>
                                        <button ${onClickk} class="btn btn-small btn-success">Crear Disponibilidad</button>
                                    </td>
                                </tr>`
                }
                if (item.status == true || item.status == false) {
                    contenido += `<tr>
                                    <td style="width: 100px;">${item.cedula}</td>
                                    <td>${item.name} ${item.last_name}</td>
                                    <td class="${item.status ? 'bg-success text-white' : 'bg-warning text-black'}">${item.status ? 'Cargada' : 'Sin Cargar'}</td>
                                    <td>
                                        <button ${!item.status ? onClickk : 'onclick="desbloquearDispoProfe(' + item.id + ', `' + texto + '`, `' + titulo + '`)"'} class="btn btn-small btn-${item.status ? 'primary' : 'info'}">${item.status ? 'Desbloquear Disponibilidad Horaria' : 'Asignar Disponibilidad Horaria'}</button>
                                    </td>
                                    ${item.status ? btnVerDisponibilidad : ''}
                                </tr>`
                }

            });
            Swal.fire({
                title: titulo,
                showConfirmButton: false,
                showCancelButton: false,
                showCloseButton: true,
                width: '1000px',
                html: `<div class="container">
                            <div class="row d-flex justify-content-center">
                                <div class="col-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" id="searchInput" onkeyup="buscar(this)" class="form-control" autocomplete="off" placeholder="Buscar">
                                        <label for="searchInput">Buscar</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="max-height: 300px; overflow: auto; width: 900px;">
                                <div class="col-12">
                                    <table class="table" id="dataTable">
                                        <tr>
                                            <thead>
                                                <th>Cedula</th>
                                                <th>Nombre y Apellido</th>
                                                <th>Disponibilidad Horaria</th>
                                            </thead>
                                        </tr>
                                        <tr>
                                            <tbody>
                                                ${contenido}
                                            </tbody>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>`
            })

        },
        error: function (error) {
            console.error(error);
        }
    });
}

function mostrarCountProfesores(activos, sin_crear, total, sin_cargar) {

    let conte = `
        <tr>
            <th colspan="4" class="text-center p-1">Disponibilidades Horarias</th>
        </tr>
        <tr class="text-center" style="cursor:pointer;">
            <td class="bg-primary text-white p-1" onclick="verDisponibilidadProfe('todos', 'Todos los Profesores')">Total de Profesores: ${total}</td>
            <td class="bg-success text-white" ${activos == 0 ? 'style="cursor:default;"' : `onclick="verDisponibilidadProfe('cargados', 'Profesores con Disponibilidad <span class=text-success>Horaria Cargada</span>')"`} >Cargadas: ${activos}</td>
            <td class="bg-danger text-white" ${sin_crear == 0 ? 'style="cursor:default;"' : `onclick="verDisponibilidadProfe('sinCrear', 'Profesores con Disponibilidad <span class=text-danger>Horaria Sin Crear</span>')"`} >Sin Crear: ${sin_crear}</td>
            <td class="bg-warning text-black" ${sin_cargar == 0 ? 'style="cursor:default;"' : `onclick = "verDisponibilidadProfe('sinCargar', 'Profesores con Disponibilidad <span class=text-warning>Horaria Sin Cargar</span>')"`} >Sin Cargar: ${sin_cargar}</td>
        </tr>`
    $('#tablaProfesores').html(conte)
    $('#tablaProfesores').removeClass('d-none')
    $('#h3Profesores').removeClass('d-none')
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
function cargarContenido() {

    let seccion = document.getElementById('selectSeccion').value
    let tipoHorario = document.getElementById('selectTipoH').value
    let semana = document.getElementById('selectSemana').value
    if (seccion != '' && tipoHorario != '' && semana == '') {
        cargarSemanas()
        return true
    }

    if (seccion != '' && tipoHorario != '' && semana != '') {
        cargarSemanas()
        $.ajax({
            url: 'crud_horario.php',
            method: "POST",
            data: {
                tipo: 'cargarHorario',
                seccion_id: seccion,
                tipo_horario: tipoHorario,
                semana: semana
            },
            success: function (res) {

                cargarBotones(res)
                $('#tabla1').removeClass('d-none')
                $('#titleTableMateria').removeClass('d-none')
                $('#titleTableMateria').text('Pensum de la Sección ' + res.titleH3[0].trayecto + res.titleH3[0].seccion + ' del PNF ' + res.titleH3[0].pnf_name)

                mostrarCountProfesores(res.activos, res.sin_crear, res.total, res.sin_cargar)

                $('#tablaHorario').removeClass('d-none')

                var select = document.getElementById("selectTipoH");
                var selectedValue = select.options[select.selectedIndex].value;
                if (selectedValue === "diurno") {
                    mostrarContenido()
                } else if (selectedValue === "nocturno") {
                    ocultarContenido()
                }
            },
            error: function (error) {
                console.error(error);
            }
        });
    }
}

// -----------------------------------------------

function pensumProfesor(pensum_id, hour, update, profesor_id, DIA, hor_id) {
    if (pensum_id == '') {
        $('#profesor-select').html('<option value="">Seleccione el Pensum</option>')
        $('#profesor-select').val('')
        $('#profesor-select').attr('disabled', true)
        $('#searchInput').attr('disabled', true)
        $('#btnAnadirExc').addClass('d-none')
        return false
    }
    comprobarSemanas(DIA)
    $.ajax({
        url: 'crud_horario.php',
        type: 'POST',
        data: {
            tipo: 'cargarProfesorPerfilProfesional',
            pensum_id: pensum_id,
            hour: hour,
            tipo_horario: $('#selectTipoH').val(),
            update: update,
            profesor_id: profesor_id,
            semana: $('#selectSemana').val(),
            sede_id: $('#selectSede').val(),
            hor_id: hor_id
        },
        success: function (res) {
            let html = ''
            let isSelected = ''
            $('#excepcionProfe').removeClass('d-none')
            $('#btnAnadirExc').removeClass('btn-warning')
            $('#btnAnadirExc').addClass('btn-primary')
            $('#btnAnadirExc').html('Añadir Excepción')
            if (!res.success || res.profesores.length == 0) {
                html = `<option value="">Ho hay Profesores Disponibles</option>`
                $("#profesor-select").attr('size', 1)
                $("#profesor-select").attr('disabled', true)
                $("#btnAnadirExc").removeAttr('disabled')
                $("#profesor-select").html(html)
                $('#btnAnadirExc').removeClass('d-none')
                $('#searchInput').attr('disabled', true)
                return false
            }
            res.profesores.forEach(item => {
                if (update) {
                    isSelected = item.id == profesor_id ? 'selected' : '';
                }
                html += `<option ${isSelected} value="${item.id}">${item.last_name} ${item.name} | CEDULA: ${item.cedula}</option>`
            });
            $("#profesor-select").attr('size', res.profesores.length)
            $("#profesor-select").html(html)

            $("#profesor-select").removeAttr('disabled')
            $('#searchInput').removeAttr('disabled')
            $('#btnAnadirExc').removeClass('d-none')
        },
        error: function (err) {
            console.log(err);
        }
    });
}

function excepcionProfe(hour, hor_id) {
    if (confirm('¿Estás seguro de crear una Excepción? Una vez guardado se reportará una alerta de que el Profesor asignado NO CUMPLE con el Perfil Profesional correspondiente para este Pensum.')) {
        $('#btnAnadirExc').attr('disabled', true)
        $('#btnAnadirExc').removeClass('btn-primary')
        $('#btnAnadirExc').addClass('btn-warning')
        $('#btnAnadirExc').html('Excepción Añadida')
        $("#profesor-select").removeAttr('disabled')
        $('#searchInput').removeAttr('disabled')
        $('#idExcepcion').val(true)
        $.ajax({
            url: 'crud_horario.php',
            type: 'POST',
            data: {
                tipo: 'traerTodosProfesoresExcepcion',
                sede_id: $('#selectSede').val(),
                tipo_horario: $('#selectTipoH').val(),
                hour: hour,
                hor_id: hor_id
            },
            success: function (res) {
                let html = ''
                if (res.profesores.length == 0) {
                    html = `<option value="">Ho hay Profesores Disponibles</option>`
                    $("#profesor-select").html(html)
                    $('#profesor-select').attr('disabled', true)
                    $('#searchInput').attr('disabled', true)
                    return false
                }
                res.profesores.forEach(item => {
                    html += `<option value="${item.id}">${item.last_name} ${item.name} | CEDULA: ${item.cedula}</option>`
                });
                $("#profesor-select").attr('size', res.profesores.length)
                $("#profesor-select").html(html)
            },
            error: function (err) {
                console.log(err);
            }
        });
    }
}

function buscar2(botn) {
    var value = $(botn).val().toLowerCase();
    $("#profesor-select option").filter(function () {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
}
function crearActualizar(datos, horaTD, horaFirstTDid) {
    let titulo = ''
    let horarioID = 0

    if (datos.update) {
        titulo = 'Actualizar Hora'
        horarioID = datos.horario_id
    } else {
        titulo = 'Crear Hora'
    }

    $.ajax({
        url: 'crud_horario.php',
        method: "POST",
        data: {
            tipo: 'showDataFormPensumAulas',
            seccion_id: document.getElementById('selectSeccion').value,
            update: datos.update,
            horario_id: horarioID,
            hour: datos.posicion,
            tipo_horario: $('#selectTipoH').val(),
            semana: $('#selectSemana').val(),
            sede_id: $('#selectSede').val()
        },
        success: function (res) {

            if (res.success) {
                let horasConsecu = ''
                if (!datos.update) {
                    horasConsecu = `<div class="row mt-3">
                                        <div class="col-12">
                                            <div class="input-group">
                                                <label for="horas-consecutivas-select" class="input-group-text">Horas Consecutivas:</label>
                                                <select id="horas-consecutivas-select" disabled onchange="comprobarHoras(${datos.posicion})" class="form-select">
                                                    ${generateOptions(res.horas_consecutivas, 0, 'horas-consecutivas')} 
                                                </select>
                                            </div>
                                        </div>
                                    </div>`
                }

                Swal.fire({
                    title: titulo + ' del Dia <span class="text-success">' + datos.dia + '</span> a la Hora <span class="text-success">' + horaTD + '</span> de la Semana <span class="text-success">' + $('#selectSemana').val() + '</span>',
                    showCloseButton: true,
                    width: '1000px',
                    html: `<div id="containerSweet" class="container" style="width: 900px;">
                    <div class="row">
                    <input type="hidden" value="false" id="idExcepcion">
                        <div class="col-12">
                            <div class="input-group">
                                <label for="pensum-select" class="input-group-text">Pensum:</label>
                                <select id="pensum-select" onchange="pensumProfesor(this.value, ${datos.posicion}, ${datos.update}, ${datos.update ? datos.profesor : 0}, '${datos.dia}', ${horarioID})" class="form-select">
                                ${generateOptions(res.pensum, datos.pensum, 'pensum')}
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3" style="max-height: 100px;">
                        <div class="col-3">
                            <div class="form-floating mb-3">
                                <input type="text" id="searchInput" disabled onkeyup="buscar2(this)" class="form-control" autocomplete="off" placeholder="Buscar">
                                <label for="searchInput">Buscar</label>
                            </div>
                        </div>
                        <div class="col-9">
                            <div class="input-group">
                                <label for="profesor-select" class="input-group-text">Profesor:</label>
                                <select id="profesor-select" style="height: 58px;" ${!datos.update ? 'disabled' : ''}  class="form-select" onchange="comprobarSemanas('${datos.dia}')">
                                    ${!datos.update ? '<option>Seleccione el Pensum</option>' : pensumProfesor(datos.pensum, datos.posicion, datos.update, datos.update ? datos.profesor : 0, datos.dia, horarioID)}
                                </select>
                                <button id="btnAnadirExc" class="btn btn-primary d-none" onclick="excepcionProfe(${datos.posicion}, ${horarioID})">
                                    Añadir Excepción
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="input-group">
                                <label for="aula-select" class="input-group-text">Aula:</label>
                                <select id="aula-select" onchange="comprobarSemanas('${datos.dia}')" class="form-select">
                                ${generateOptions(res.aulas, datos.aula, 'aulas')}
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                        <h3 class="h3">Duración:</h3>
                        <div class="input-group">
                            <label for="semana-inicio-select" class="input-group-text">Semana Inicio:</label>
                            <select id="semana-inicio-select" class="form-control" disabled>
                                <option value="${datos.update ? res.semanas.semana_inicio : $("#selectSemana").val()}">${datos.update ? res.semanas.semana_inicio : $("#selectSemana").val()}</option>
                            </select>
                        </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                        <div class="input-group">
                            <label for="semana-fin-select" class="input-group-text">Semana Fin:</label>
                            <select id="semana-fin-select" onchange="comprobarSemanas('${datos.dia}')" class="form-select">
                                ${generateOptions(res.semanas.semana_fin, 0, 'semana-inicio-fin')}
                            </select>
                        </div>
                        </div>
                    </div>${horasConsecu}</div>`,
                    confirmButtonText: 'Guardar',
                    focusConfirm: false,
                    preConfirm: () => {

                        if (!datos.update) {
                            if (!$("#profesor-select").val() || !$("#aula-select").val() || !$("#pensum-select").val() || !$("#semana-inicio-select").val() || !$("#semana-fin-select").val() || !$("#horas-consecutivas-select").val()) {
                                styleInputs([$("#profesor-select"), $("#aula-select"), $("#pensum-select"), $("#semana-inicio-select"), $("#semana-fin-select"), $("#horas-consecutivas-select")])
                                Swal.showValidationMessage('Por favor, seleccione todos los campos');
                                return false;
                            }
                        } else {
                            if (!$("#profesor-select").val() || !$("#aula-select").val() || !$("#pensum-select").val() || !$("#semana-inicio-select").val() || !$("#semana-fin-select").val()) {
                                styleInputs([$("#profesor-select"), $("#aula-select"), $("#pensum-select"), $("#semana-inicio-select"), $("#semana-fin-select"), $("#horas-consecutivas-select")])
                                Swal.showValidationMessage('Por favor, seleccione todos los campos');
                                return false;
                            }
                        }

                        if (!comprobarHoras(datos.posicion)) {
                            return false
                        }

                        if (!datos.update) {
                            $.ajax({
                                url: 'crud_horario.php',
                                method: "POST",
                                data: {
                                    tipo: 'crear',
                                    profesor_id: $("#profesor-select").val(),
                                    director_id: 1,
                                    aula_id: $("#aula-select").val(),
                                    pensum_id: $("#pensum-select").val(),
                                    hour: datos.posicion,
                                    tipo_horario: $("#selectTipoH").val(),
                                    sede_id: $("#selectSede").val(),
                                    seccion_id: $("#selectSeccion").val(),
                                    semana_inicio: $("#semana-inicio-select").val(),
                                    semana_fin: $("#semana-fin-select").val(),
                                    horas_consecutivas: $("#horas-consecutivas-select").val(),
                                    excepcion: $('#idExcepcion').val(),
                                    horaFirstTDid: horaFirstTDid,
                                    diaText: datos.dia
                                },
                                success: function () {
                                    cargarContenido()
                                    cargarSemanas()
                                    let textHoras = ''
                                    if ($("#horas-consecutivas-select").val() > 1) {
                                        textHoras = '¡Horas Creadas!'
                                    } else {
                                        textHoras = '¡Hora Creada!'
                                    }
                                    Swal.fire({
                                        timer: 5000,
                                        timerProgressBar: true,
                                        position: 'bottom-start',
                                        toast: true,
                                        showConfirmButton: false,
                                        icon: 'success',
                                        title: textHoras,
                                    })
                                },
                                error: function (error) {
                                    console.error(error);
                                }
                            });
                        } else {
                            $.ajax({
                                url: 'crud_horario.php',
                                method: "POST",
                                data: {
                                    tipo: 'actualizar',
                                    horario_id: datos.horario_id,
                                    profesor_id: $("#profesor-select").val(),
                                    director_id: 1,
                                    aula_id: $("#aula-select").val(),
                                    pensum_id: $("#pensum-select").val(),
                                    hour: datos.posicion,
                                    semana_inicio: $("#semana-inicio-select").val(),
                                    semana_fin: $("#semana-fin-select").val(),
                                    excepcion: $('#idExcepcion').val(),
                                    horaFirstTDid: horaFirstTDid,
                                    diaText: datos.dia
                                },
                                success: function () {
                                    cargarContenido()
                                    cargarSemanas()
                                    Swal.fire({
                                        timer: 5000,
                                        timerProgressBar: true,
                                        position: 'bottom-start',
                                        toast: true,
                                        showConfirmButton: false,
                                        icon: 'success',
                                        title: '¡Hora Actualizada!',
                                    })
                                },
                                error: function (error) {
                                    console.error(error);
                                }
                            });
                        }


                    }
                })
                if (!datos.update) {
                    Swal.disableButtons()
                }
            }
            if (res.status) {
                switch (res.status) {
                    case 2:
                        Swal.fire({
                            timer: 5000,
                            timerProgressBar: true,
                            position: 'bottom-start',
                            toast: true,
                            showConfirmButton: false,
                            icon: 'info',
                            title: res.message,
                        })
                        break;
                    case 3:
                        Swal.fire({
                            timer: 5000,
                            timerProgressBar: true,
                            position: 'bottom-start',
                            toast: true,
                            showConfirmButton: false,
                            icon: 'info',
                            title: res.message,
                        })
                        break;
                }
            }
        },
        error: function (error) {
            console.error(error);
        }
    });

    function styleInputs(inputs) {
        inputs.forEach(item => {
            if (item.val() === '') {
                item.addClass('is-invalid')
            } else {
                item.removeClass('is-invalid')
            }
        });
        return true
    }

    function generateOptions(options, selectedValue, tipoOption) {

        var html = '<option value="">Seleccione</option>';
        switch (tipoOption) {
            case 'profesores':
                options.forEach(function (option) {
                    var isSelected = option.id == selectedValue ? 'selected' : '';
                    html += '<option value="' + option.id + '" ' + isSelected + '>' + option.name + ' ' + option.last_name + ' | ' + option.perfil + '</option>';
                })
                return html;
            case 'aulas':
                options.forEach(function (option) {
                    var isSelected = option.id == selectedValue ? 'selected' : '';
                    html += '<option value="' + option.id + '" ' + isSelected + '>' + option.name + ' | Capacidad: ' + option.capacity + '</option>';
                })
                return html;

            case 'pensum':
                options.forEach(function (option) {
                    var isSelected = option.id == selectedValue ? 'selected' : '';
                    html += `<option value="${option.id}" ${isSelected}>COD: ${option.codigo} | MATERIA: ${option.materia} | UC: ${option.uc} | HORAS: ${option.horas}</option>`
                })
                return html;

            case 'semana-inicio-fin':
                for (let i = $("#selectSemana").val(); i < 36; i++) {
                    var isSelected = i == options ? 'selected' : '';
                    html += '<option value="' + i + '"' + isSelected + '>' + i + '</option>';
                }
                return html;

            case 'horas-consecutivas':
                html = ''
                for (let i = 1; i < 5; i++) {
                    if (i == 1) {
                        html += '<option value="' + i + '" ' + isSelected + '>Hora ' + i + '</option>';
                    } else {
                        html += '<option value="' + i + '" ' + isSelected + '>Horas ' + i + '</option>';
                    }
                }
                return html;
        }

    }
}

function cambiarEstado(td) {
    let datos = JSON.parse(td.getAttribute("data-coleccion"));
    let horaTD = $(td).closest("tr").find("td:first").text();
    let horaFirstTDid = $(td).closest("tr").find("td:first").attr('data-id')

    if (datos.update) {
        Swal.fire({
            toast: true,
            showConfirmButton: false,
            showCancelButton: false,
            showCloseButton: true,
            title: '<h1 class="h1 d-flex justify-content-center">Opciones</h1>',
            html: `<h3 class="h3">Datos:</h3><div class="d-grid gap-2">
                Profesor: ${datos.profesor_name} ${datos.profesor_last_name} </br>
                Aula: ${datos.aula_name} | Capacidad: ${datos.aula_capacity} </br>
                Materia: ${datos.pensum_name} </br>
                Semana Incio: ${datos.semana_inicio}</br>
                Semana Fin: ${datos.semana_fin}
                <button class="btn btn-success" onclick='crearActualizar(${JSON.stringify(datos)}, "${horaTD}", ${horaFirstTDid})'>Actualizar</button>
                <button class="btn btn-danger" onclick="borrar(${datos.horario_id})">Borrar</button>
                </div>`,
        })
        return true
    } else {
        crearActualizar(datos, horaTD, horaFirstTDid)
    }
}

function verificarHorasSeguidas(dia, pensum) {
    var buttons = document.querySelectorAll('.hour-button');
    let limited = 0
    buttons.forEach(function (button) {
        var coleccion = JSON.parse(button.dataset.coleccion)
        if (coleccion.dia == dia && coleccion.pensum == pensum) {
            limited++
        }
    })
    if (limited >= 4) {
        return true
    }
    return false
}

function comprobarSemanas(DIAS) {

    if ($("#pensum-select").val() != '') {
        if (verificarHorasSeguidas(DIAS, $("#pensum-select").val())) {
            Swal.showValidationMessage('No puedes agregar más horas de este Pensum en el dia ' + DIAS);
            $("#pensum-select").addClass('is-invalid')
            Swal.disableButtons()
            return false;
        } else {
            Swal.enableButtons()
            Swal.resetValidationMessage()
            $("#pensum-select").removeClass('is-invalid')
        }
    }

    if ($("#profesor-select").val() == '' || $("#aula-select").val() == '' || $("#pensum-select").val() == '' || $("#semana-inicio-select").val() == '' || $("#semana-fin-select").val() == '') {
        $('#horas-consecutivas-select').prop('disabled', true)
        Swal.disableButtons()
        return true
    } else {
        $('#horas-consecutivas-select').removeAttr('disabled')
        Swal.enableButtons()
    }
    if (parseInt($('#semana-inicio-select').val()) > parseInt($('#semana-fin-select').val())) {
        $('#semana-inicio-select').addClass('is-invalid')
        $('#semana-fin-select').addClass('is-invalid')
        Swal.showValidationMessage('La Semana Fin no puede ser menor a la Semana de Inicio')
        Swal.disableButtons()
        return true
    } else {
        $('#semana-inicio-select').removeClass('is-invalid')
        $('#semana-fin-select').removeClass('is-invalid')
        Swal.enableButtons()
        Swal.resetValidationMessage()
        return true
    }
}
function comprobarHoras(posicion) {
    if ($("#horas-consecutivas-select").val() == 1) {
        Swal.enableButtons()
        Swal.resetValidationMessage()
        $('#horas-consecutivas-select').removeClass('is-invalid')
        return true
    }
    const posicionOriginal = posicion
    for (let i = 0; i < $("#horas-consecutivas-select").val(); i++) {

        var buttons = document.querySelectorAll('.hour-button');

        buttons.forEach(function (button) {
            var coleccion = JSON.parse(button.dataset.coleccion)

            if (parseInt(coleccion.posicion) == parseInt(posicion) && coleccion.update) {
                Swal.showValidationMessage('Una de la siguientes Horas Consecutivas ya esta Asignada, intenta con menos')
                Swal.disableButtons()
                $('#horas-consecutivas-select').addClass('is-invalid')
                return false
            }
            if (parseInt(coleccion.posicion) == parseInt(posicion) && !coleccion.update) {
                Swal.resetValidationMessage()
                Swal.enableButtons()
                $('#horas-consecutivas-select').removeClass('is-invalid')
            }

            if (parseInt(coleccion.posicion) == parseInt(posicion)) {
                $.ajax({
                    url: 'crud_horario.php',
                    method: "POST",
                    data: {
                        tipo: 'verificarDatosHorasConsecutivas',
                        profesor_id: $("#profesor-select").val(),
                        aula_id: $("#aula-select").val(),
                        pensum_id: $("#pensum-select").val(),
                        hour: posicionOriginal,
                        tipo_horario: $("#selectTipoH").val(),
                        semana_inicio: $("#semana-inicio-select").val(),
                        semana_fin: $("#semana-fin-select").val(),
                        horas_consecutivas: $("#horas-consecutivas-select").val()
                    },
                    success: function (res) {
                        // console.log(res);
                        if (res.profesor.status == 2) {
                            Swal.showValidationMessage(res.profesor.message)
                            Swal.disableButtons()
                            return false
                        }
                        if (res.profesor.status == 3) {
                            Swal.showValidationMessage(res.profesor.message)
                            Swal.disableButtons()
                            return false
                        }
                        if (res.aula.status == 2) {
                            Swal.showValidationMessage(res.aula.message)
                            Swal.disableButtons()
                            return false
                        }
                        if (res.pensum.status == 2) {
                            Swal.showValidationMessage(res.pensum.message)
                            Swal.disableButtons()
                            return false
                        }
                        if (res.profesor.status == 1 && res.aula.status == 1 && res.pensum.status == 1) {
                            Swal.resetValidationMessage()
                            Swal.enableButtons()
                            return true
                        }
                    },
                    error: function (error) {
                        console.error(error);
                    }
                });
            }

        })
        posicion += 7
    }
    return true
}
