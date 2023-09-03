<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
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
    <title>Disponibilidad</title>
    <link rel="shortcut icon" href="../../../img/iconiut.png" type="image/x-icon">

    <style>
        .hour-button {
            transition: 0.3s ease;
        }

        .cursor-td-pointer {
            cursor: pointer;
        }

        .cursor-td-default {
            cursor: default;
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
                <img class="img-fluid" src="../../../img/banneriut.png" alt="UPTAI">
            </div>
        </div>
        <div class="row mt-4 d-flex aling-items-center">
            <div class="col-md-8 col-12">
                <h1 id="nombreProfesor" class="h1"></h1>
            </div>
            <div class="col-md-4 col-12 d-flex align-items-end justify-content-start">
                <h3 id="h3Status" class="h3"></h3>
                <div id="divSave" class="ms-3">
                </div>
            </div>

        </div>
        <div class="row">
            <div class="d-flex align-items-center col-md-8 col-6">
                <a href="../" class="btn btn-secondary">Regresar</a>
            </div>
            <div class="col-md-4 col-6">
                <label for="tipoHorario">Seleccione su tipo de Disponibilidad</label>
                <select class="form-select" id="tipoHorario" onchange="mostrarTitulo()">
                    <option value="">Selecciona</option>
                    <option value="diurno">Diurno</option>
                    <option value="nocturno">Nocturno</option>
                </select>
            </div>
        </div>
        <input type="hidden" id="statuss" value="0">
        <div class="row mt-3">

        </div>
        <div class="row mt-3">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table d-none" id="tableMain">
                        <thead>
                            <tr>
                                <th style="width: 150px;" class="bg-primary text-white">Hora</th>
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
<script src="js_disponibilidad.js">
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

</html>