function cargarUsuarios() {
    $.ajax({
        type: "POST",
        url: "crud_usuarios.php",
        data: {
            tipo: 'listar'
        },
        success: function (res) {
            let html = ''

            if (res.usuarios_adm_coor.length == 0 && res.usuarios_prof.length == 0) {
                html = '<tr><td colspan="4">No hay registros</td></tr>'
                $('#tablaUsuarios').html(html)
                return false
            }

            res.usuarios_adm_coor.forEach(item => {
                html += `<tr>
                            <td>${item.user_name}</td>
                            <td>${item.sede}</td>
                            <td>${typeUse(item.user_type, item.pnf)}</td>
                            <td>${item.status == true ? '<span class="text-success">Activo</span>' : '<span class="text-danger">Deshabilitado</span>'}</td>
                            <td>${item.status == true ? `<button class="btn btn-danger" onclick="deshabilitarHabilitar(${item.id}, '${item.user_name}', true)">Deshabilitar</button>` : `<button class="btn btn-success" onclick="deshabilitarHabilitar(${item.id}, '${item.user_name}', false)">Habilitar</button>`}</td>
                            <td><button class="btn btn-secondary" onclick="cambiarContra(${item.id})">Cambiar Contraseña</button></td>
                            <td><button class="btn btn-primary" onclick="modificar(${item.id}, '${item.user_type}', '${item.user_name}')">Modificar</button></td>
                            <td><button class="btn btn-danger" onclick="eliminar(${item.id})">Eliminar</button></td>
                        </tr>`
            })

            res.usuarios_prof.forEach(item => {
                html += `<tr>
                            <td>${item.user_name}</td>
                            <td>${item.sede}</td>
                            <td>${typeUse(item.user_type, item.name + ' ' + item.last_name)}</td>
                            <td>${item.status == true ? '<span class="text-success">Activo</span>' : '<span class="text-danger">Deshabilitado</span>'}</td>
                            <td>${item.status == true ? `<button class="btn btn-danger" onclick="deshabilitarHabilitar(${item.id}, '${item.user_name}', true)">Deshabilitar</button>` : `<button class="btn btn-success" onclick="deshabilitarHabilitar(${item.id}, '${item.user_name}', false)">Habilitar</button>`}</td>
                            <td><button class="btn btn-secondary" onclick="cambiarContra(${item.id})">Cambiar Contraseña</button></td>
                        </tr>`
            })

            $('#tablaUsuarios').html(html)
        }
    })
    function typeUse(tipo, name) {
        switch (tipo) {
            case 'admin':
                return '<span class="text-primary">Administrador</span>'
            case 'coord':
                return '<span class="text-success">Coordinador</span><br>PNF ' + name
            case 'prof':
                return '<span class="text-warning">Profesor</span><br>' + name
        }
    }
}

function cargarSelectPNF(update, id) {
    $.ajax({
        url: 'crud_usuarios.php',
        type: 'POST',
        data: {
            tipo: 'listar_pnfs',
        },
        success: function (res) {
            let option = '<option value="">Seleccione</option>'
            let seleccionado = ''
            res.pnfs.forEach(function (item) {
                if (update) {
                    seleccionado = item.id == id ? 'selected' : ''
                }
                option += '<option ' + seleccionado + ' value="' + item.id + '">' + item.name + '</option>'
            })
            $('#pnfID').html(option)
            return true
        },
        error: function (err) {
            console.log(err);
        }
    });
}

function cargarSelectProfesor(update, id) {
    $.ajax({
        url: 'crud_usuarios.php',
        type: 'POST',
        data: {
            tipo: 'listar_profesores',
        },
        success: function (res) {
            let option = ''
            let seleccionado = ''
            res.profesores.forEach(function (item) {
                if (update) {
                    seleccionado = item.id == id ? 'selected' : ''
                }
                option += '<option ' + seleccionado + ' value="' + item.id + '">' + `${item.name} ${item.last_name} | Cedula: ${item.cedula}` + '</option>'
            })
            $('#profesorID').html(option)
            return true
        },
        error: function (err) {
            console.log(err);
        }
    });
}

function cargarSelectSede(update, id) {
    $.ajax({
        url: 'crud_usuarios.php',
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

function cargarSelectTipoUsuario(update, tipo) {
    const tipos = [
        { 'tipo': 'admin', 'name': 'Administrador' },
        { 'tipo': 'coord', 'name': 'Coordinador' },
        { 'tipo': 'prof', 'name': 'Profesor' }
    ]
    let option = '<option value="">Seleccione</option>'
    let seleccionado = ''
    tipos.forEach(item => {
        if (update) {
            seleccionado = item.tipo == tipo ? 'selected' : ''
        }
        option += '<option ' + seleccionado + ' value="' + item.tipo + '">' + item.name + '</option>'
    });
    return option
}

function selectTypeUser(typ) {
    if (typ == 'admin') {

        let sele = `<select id="sedeID" class="form-select mb-3">
                        ${cargarSelectSede(false, 0)}
                    </select><label for="sedeID">Sede</label>`

        $('#divSelectSede').html(sele)
        $('#divSelectSede').removeClass('d-none')

        $('#divSelect').addClass('d-none')
        $('#divSelect').html('')
        $('#divUserName').html('<input id="userName" autocomplete="off" type="text" class="form-control" placeholder=""><label for="userName">Nombre del Usuario</label>')
        return false
    }
    if (typ == 'coord') {

        let sele = `<select id="sedeID" class="form-select mb-3">
                        ${cargarSelectSede(false, 0)}
                    </select><label for="sedeID">Sede</label>`

        $('#divSelectSede').html(sele)
        $('#divSelectSede').removeClass('d-none')
        let html = `<div class="form-floating mb-3"><select id="pnfID" class="form-select">
                    ${cargarSelectPNF(false, 0)}
                    </select><label for="pnfID">PNF</label></div>`
        $('#divSelect').html(html)
        $('#divSelect').removeClass('d-none')
        $('#divUserName').html('<input id="userName" autocomplete="off" type="text" class="form-control" placeholder=""><label for="userName">Nombre del Usuario</label>')
        return true
    }
    if (typ == 'prof') {
        let html = `<div class="input-group">
                        <div class="form-floating mb-3">
                            <input type="text" id="buscar2" onkeyup="buscar2(this)" placeholder="" class="form-control">
                            <label for="buscar2">Buscar Profesor</label>
                        </div>
                        <div class="form-floating mb-3">
                            <select id="profesorID" multiple class="form-select">
                                ${cargarSelectProfesor(false, 0)}
                            </select>
                            <label for="buscar2">Profesor</label>
                        </div>
                    </div>`
        $('#divSelect').html(html)
        $('#divSelectSede').html('')
        $('#divSelectSede').addClass('d-none')
        $('#divSelect').removeClass('d-none')
        $('#divUserName').html('<span class="text-white bg-info badge text-wrap fs-4">El nombre de Usuario de los Profesores es su N° de <span class="text-primary">Cedula</span></span>')
        return true
    }
}



function crearUsuario(sedeID, tipo_usuario, userName, contra, pnfID, profesorID) {
    $.ajax({
        type: "POST",
        url: "crud_usuarios.php",
        data: {
            tipo: 'crear',
            sede_id: sedeID,
            pnf_id: pnfID,
            profesor_id: profesorID,
            user_type: tipo_usuario,
            user_name: userName,
            password: contra
        },
        success: function () {
            Swal.fire({
                timer: 5000,
                timerProgressBar: true,
                position: 'bottom-start',
                toast: true,
                showConfirmButton: false,
                icon: 'success',
                title: 'Usuario Creado'
            })
            cargarUsuarios()
        }
    });
}

function buscar2(botn) {
    var value = $(botn).val().toLowerCase();
    $("#profesorID option").filter(function () {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
}

function buscar(botn) {
    var value = $(botn).val().toLowerCase();
    $("#dataTable tbody tr").filter(function () {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
}

function mostrarFormulario() {
    Swal.fire({
        title: 'Crear Usuario',
        width: '1000px',
        html: `<div class="form-floating"><select id="tipo_usuario" onchange="selectTypeUser(this.value)" class="form-select mb-3">
                ${cargarSelectTipoUsuario(false, 0)}
            </select><label for="tipo_usuario">Tipo de Usuario</label></div><div class="d-none" id="divSelect"></div>`+
            '<div class="form-floating d-none" id="divSelectSede"></div>' +
            '<div class="form-floating mb-3" id="divUserName"><input id="userName" autocomplete="off" type="text" class="form-control" placeholder=""><label for="userName">Nombre del Usuario</label></div>' +
            '<div class="form-floating mb-3"><input type="password" minlength="8" id="contra" autocomplete="off" class="form-control" placeholder=""><label for="contra">Contraseña</label></div>',
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Crear',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {

            if ($('#pnfID').length > 0) {
                if ($('#sedeID').val() == '' || $('#tipo_usuario').val() == '' || $('#pnfID').val() == '' || $('#userName').val() == '' || $('#contra').val() == '') {
                    Swal.showValidationMessage('Por favor, completa todos los campos');
                    return false;
                }
                if ($('#contra').val() < 8) {
                    Swal.showValidationMessage('La Contraseña tiene que ser MAYOR a 8 digitos');
                    $('#contra').addClass('is-invalid');

                    return false;
                }
                crearUsuario($('#sedeID').val(), $('#tipo_usuario').val(), $('#userName').val(), $('#contra').val(), $('#pnfID').val(), null);

            } else if ($('#profesorID').length > 0) {
                if ($('#tipo_usuario').val() == '' || $('#profesorID').val() == '' || $('#contra').val() == '') {
                    Swal.showValidationMessage('Por favor, completa todos los campos');
                    return false;
                }
                if ($('#contra').val() < 8) {
                    Swal.showValidationMessage('La Contraseña tiene que ser MAYOR a 8 digitos');
                    $('#contra').addClass('is-invalid');

                    return false;
                }
                crearUsuario(null, $('#tipo_usuario').val(), null, $('#contra').val(), null, $('#profesorID').val())
            } else {
                if ($('#sedeID').val() == '' || $('#tipo_usuario').val() == '' || $('#userName').val() == '' || $('#contra').val() == '') {
                    Swal.showValidationMessage('Por favor, completa todos los campos');
                    return false;
                }
                if ($('#contra').val() < 8) {
                    Swal.showValidationMessage('La Contraseña tiene que ser MAYOR a 8 digitos');
                    $('#contra').addClass('is-invalid');

                    return false;
                }
                crearUsuario($('#sedeID').val(), $('#tipo_usuario').val(), $('#userName').val(), $('#contra').val(), null, null);
            }
        }
    });

}

$(document).ready(function () {
    cargarUsuarios();
});


function deshabilitarHabilitar(id, name, siono) {
    let titulo
    let texto
    let condi
    if (siono) {
        titulo = 'Deshabilitar Usuario ' + name
        texto = '<span>¿Estas seguro de deshabilitar este Usuario?, no podra iniciar sesión</span>'
        condi = false
    } else {
        titulo = 'Habilitar Usuario ' + name
        texto = 'Confirma'
        condi = true
    }

    Swal.fire({
        toast: true,
        showConfirmButton: true,
        showCancelButton: true,
        showCloseButton: true,
        title: '<h4 class="h4 d-flex justify-content-center">' + titulo + '</h4>',
        html: texto,
        confirmButtonText: 'Si',
        denyButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            deshabilitarHabilitarAjax(id, condi)
        }
    })
    function deshabilitarHabilitarAjax(id, condi) {
        $.ajax({
            type: "POST",
            url: "crud_usuarios.php",
            data: {
                tipo: 'deshabilitar',
                id: id,
                condi: condi
            },
            success: function () {
                let titu
                if (condi) {
                    titu = 'Habilitado'
                } else {
                    titu = 'Deshabilitado'
                }
                Swal.fire({
                    timer: 5000,
                    timerProgressBar: true,
                    position: 'bottom-start',
                    toast: true,
                    showConfirmButton: false,
                    icon: 'success',
                    title: 'Usuario ' + titu
                })
                cargarUsuarios()
            }
        })
    }
}


function cambiarContra(id) {
    Swal.fire({
        title: 'Cambiar Contraseña',
        showDenyButton: true,
        showCancelButton: false,
        confirmButtonText: `Guardar`,
        denyButtonText: `Cancelar`,
        html: `<div class="form-floating mb-3">
                    <input type="password" minlength="8" id="contra1" autocomplete="off" class="form-control" placeholder="">
                    <label for="contra1">Ingrese la Contraseña Nueva</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" minlength="8" id="contra2" autocomplete="off" class="form-control" placeholder="">
                    <label for="contra2">Confirme la Contraseña</label>
                </div>`,
        preConfirm: () => {
            if ($('#contra1').val() == '' || $('#contra2').val() == '') {
                Swal.showValidationMessage('Por favor, completa todos los campos');
                return false;
            }
            if ($('#contra1').val() != $('#contra2').val()) {
                Swal.showValidationMessage('Las Contraseñas no Coiciden');
                return false;
            }
            cambiarContraAjax(id, $('#contra1').val())
        }
    })
}
function cambiarContraAjax(id, contra) {
    $.ajax({
        type: "POST",
        url: "crud_usuarios.php",
        data: {
            tipo: 'cambiar_contra',
            id: id,
            contra: contra
        },
        success: function () {
            Swal.fire({
                timer: 5000,
                timerProgressBar: true,
                position: 'bottom-start',
                toast: true,
                showConfirmButton: false,
                icon: 'success',
                title: 'Contraseña Modificada'
            })
        }
    })
}

function modificar(id, tipo, user_name) {
    if (tipo == 'admin') {
        $.ajax({
            type: "POST",
            url: "crud_usuarios.php",
            data: {
                tipo: 'listar_update',
                id: id,
                user_tipo: tipo
            },
            success: function (res) {
                Swal.fire({
                    title: 'Modificar Usuario ' + user_name,
                    showDenyButton: true,
                    showCancelButton: false,
                    confirmButtonText: `Guardar`,
                    denyButtonText: `Cancelar`,
                    html: `<div class="form-floating mb-3">
                                <input type="text" value="${res.usuario[0].user_name}" minlength="8" id="name" autocomplete="off" class="form-control" placeholder="">
                                <label for="name">Nombre</label>
                            </div>
                            <div class="form-floating mb-3">
                                <select id="sedeID" class="form-select mb-3">
                                    ${cargarSelectSede(true, res.usuario[0].sede_id)}
                                </select><label for="sedeID">Sede</label>
                            </div>`,
                    preConfirm: () => {
                        if ($('#name').val() == '' || $('#sedeID').val() == '') {
                            Swal.showValidationMessage('Por favor, completa todos los campos');
                            return false;
                        }
                        modificarAjax(id, $('#name').val(), $('#sedeID').val(), null, tipo)
                    }
                })
            }
        });
    }
    if (tipo == 'coord') {
        $.ajax({
            type: "POST",
            url: "crud_usuarios.php",
            data: {
                tipo: 'listar_update',
                id: id,
                user_tipo: tipo
            },
            success: function (res) {
                Swal.fire({
                    title: 'Modificar Usuario ' + user_name,
                    showDenyButton: true,
                    showCancelButton: false,
                    confirmButtonText: `Guardar`,
                    denyButtonText: `Cancelar`,
                    html: `<div class="form-floating mb-3">
                                <input type="text" value="${res.usuario[0].user_name}" minlength="8" id="name" autocomplete="off" class="form-control" placeholder="">
                                <label for="name">Nombre</label>
                            </div>
                            <div class="form-floating mb-3">
                                <select id="sedeID" class="form-select mb-3">
                                    ${cargarSelectSede(true, res.usuario[0].sede_id)}
                                </select><label for="sedeID">Sede</label>
                            </div>
                            <div class="form-floating mb-3">
                                <select id="pnfID" class="form-select">
                                    ${cargarSelectPNF(true, res.usuario[0].pnf_id)}
                                </select><label for="pnfID">PNF</label>
                            </div>`,
                    preConfirm: () => {
                        if ($('#name').val() == '' || $('#sedeID').val() == '' || $('#pnfID').val() == '') {
                            Swal.showValidationMessage('Por favor, completa todos los campos');
                            return false;
                        }
                        modificarAjax(id, $('#name').val(), $('#sedeID').val(), $('#pnfID').val(), tipo)
                    }
                })
            }
        });
    }
    function modificarAjax(id, name, sede_id, pnf_id, tipo_user) {
        $.ajax({
            type: "POST",
            url: "crud_usuarios.php",
            data: {
                tipo: 'modificar_ajax',
                id: id,
                name: name,
                sede_id: sede_id,
                pnf_id: pnf_id,
                tipo_user: tipo_user
            },
            success: function () {
                Swal.fire({
                    timer: 5000,
                    timerProgressBar: true,
                    position: 'bottom-start',
                    toast: true,
                    showConfirmButton: false,
                    icon: 'success',
                    title: 'Usuario Modificado'
                })
                cargarUsuarios()
            }
        });
    }
}
function eliminar(id) {

    Swal.fire({
        toast: true,
        showConfirmButton: true,
        showCancelButton: true,
        showCloseButton: true,
        title: '<h4 class="h4 d-flex justify-content-center">¿Estas seguro de Eliminar este Usuario?</h4>',
        confirmButtonText: 'Si',
        denyButtonText: 'No',
        preConfirm: () => {
            $.ajax({
                type: "POST",
                url: "crud_usuarios.php",
                data: {
                    tipo: 'eliminar',
                    id: id
                },
                success: function () {
                    Swal.fire({
                        timer: 5000,
                        timerProgressBar: true,
                        position: 'bottom-start',
                        toast: true,
                        showConfirmButton: false,
                        icon: 'success',
                        title: 'Usuario Eliminado'
                    })
                    cargarUsuarios()
                }
            });
        }
    })


}