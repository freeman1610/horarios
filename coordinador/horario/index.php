<?php
session_start();

if (!isset($_SESSION['user_id']) && !isset($_SESSION['user_type'])) {
    header("Location: ../../login/index.php");
    exit();
}
if ($_SESSION['user_type'] != 'coord') {
    echo 'No tienes permisos para este modulo';
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <title>Horarios</title>
    <link rel="shortcut icon" href="../../img/iconiut.png" type="image/x-icon">
    <style>
        .hour-button {
            transition: 0.3s ease;
            cursor: pointer;
        }

        .hour-button.dragging {
            opacity: 0.5;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row mt-4">
            <div class="col-12">
                <img class="img-fluid" src="../../img/banneriut.png" alt="UPTAI">
            </div>
        </div>
        <div class="row pt-3">
            <div class="col-md-3 col-5">
                <h1 class="h1">Horarios</h1>
            </div>
            <div class="col-md-2 col-5 d-flex align-items-center">
                <a class="btn btn-secondary" href="../">Regresar</a>
            </div>
        </div>
        <div class="row d-flex justify-content-center">
            <div class="col-md-3 col-12">
                <label for="selectSeccion">Seleccione la Sección</label>
                <select class="form-select" id="selectSeccion" onchange="cargarContenido()">
                    <option value="">Seleccione</option>
                </select>
            </div>
            <div class="col-md-3 col-12">
                <label for="selectSeccion">Seleccione el Turno</label>
                <select class="form-select" id="selectTipoH" onchange="cargarContenido()">
                    <option value="">Seleccione</option>
                    <option value="diurno">Diurno</option>
                    <option value="nocturno">Nocturno</option>
                </select>
            </div>
            <div class="col-md-3 col-12">
                <label for="selectSeccion">Seleccione la Semana</label>
                <select class="form-select" id="selectSemana" onchange="cargarContenido()" disabled="true">
                    <option value="">Seleccione</option>
                </select>
            </div>
        </div>
        <div class="row mt-1">
            <div class="col-12">
                <h3 class="h3 d-none mt-2" id="titleTableMateria"></h3>
                <div class="table-responsive">
                    <table class="table d-none" id="tabla1">
                        <thead>
                            <tr>
                                <th>Codigo</th>
                                <th>Materia</th>
                                <th>Profesor</th>
                                <th>Unidades de Credito</th>
                                <th>Horas</th>
                                <th>Semanas</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyTablaMaterias1"></tbody>
                    </table>
                </div>
                <h3 class="h3 d-none mt-1" id="h3Profesores">Profesores</h3>
                <table id="tablaProfesores" class="table d-none">
                </table>
                <h3 class="h3 d-none mt-1" id="h3Horario"></h3>
                <div class="table-responsive">
                    <table id="tablaHorario" class="table d-none">
                        <thead>
                            <tr>
                                <th class="bg-primary text-white">Hora</th>
                                <th class="bg-info text-white">Lunes</th>
                                <th class="bg-info text-white">Martes</th>
                                <th class="bg-info text-white">Miércoles</th>
                                <th class="bg-info text-white">Jueves</th>
                                <th class="bg-info text-white">Viernes</th>
                                <th class="bg-warning text-white">Sabado</th>
                                <th class="bg-warning text-white">Domingo</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyTablaMaterias2">
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>


</body>
<!-- <script>
    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    if (isMobileDevice()) {
        alert('Estás accediendo desde un teléfono.');
    } else {
        alert('No estás accediendo desde un teléfono.');
    }
</script> -->
<script src="https://code.jquery.com/jquery-3.7.0.js" integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM=" crossorigin="anonymous"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js.js">
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

</html>