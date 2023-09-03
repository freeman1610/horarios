
function cargarExcepciones() {
    $.ajax({
        type: "POST",
        url: "excepciones.php",
        data: {
            tipo: 'cargarExcepciones'
        },
        success: function (res) {
            let html = ''

            res.datos.forEach(item => {
                html += `<tr>
                            <td class="lh-lg">El Usuario <span onclick="profesor(${item.user_id})" class="btn btn-primary btn-sm">${item.user_name}</span> añadio una excepción en el Horario de la <strong>Sección ${item.seccion_trayecto}${item.seccion} del PNF ${item.seccion_pnf} (SEDE: ${item.seccion_sede})</strong> con el Profesor <span class="btn btn-secondary btn-sm" onclick="profesor(${item.profesor_id})">${item.prof_name} ${item.prof_last_name} | Cedula ${item.cedula}</span> el Dia <strong>${item.dia_text}</strong> en la Hora <strong>${item.hora_text}</strong>, donde se le asigno la Materia  <span class="btn btn-success btn-sm" onclick="materia(${item.pensum_id})">${item.materia} | Codigo: ${item.codigo}</span>, la cual el Profesor NO CUMPLE CON EL PERFIL PROFESIONAL REQUERIDO. <strong>Fecha de la acción: ${item.fecha}</strong></td>
                        </tr>`
            });
            $('#tablaExcepciones').html(html)
        }
    });
}


$(document).ready(function () {
    cargarExcepciones()
})