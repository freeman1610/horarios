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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profesores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="shortcut icon" href="../../img/iconiut.png" type="image/x-icon">
    <style>
        ul {
            list-style: none;
            padding: 0;
        }

        li {
            padding: 5px;
        }

        li:hover {
            background-color: #f0f0f0;
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
        <div class="row align-items-center mt-4">
            <div class="col-md-4 col-5">
                <h1 class="h1">Profesores</h1>
            </div>
            <div class="col-md-2 mb-2 col-5">
                <a class="btn btn-secondary" href="../">Regresar</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 col-12">
                <div class="form-floating mb-3">
                    <input type="text" id="searchInput" onkeyup="buscar(this)" class="form-control" autocomplete="off" placeholder="Buscar">
                    <label for="searchInput">Buscar</label>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table mt-4" id="dataTable">
                    <thead>
                        <tr>
                            <th>Apellido</th>
                            <th>Nombre</th>
                            <th>Cedula</th>
                            <th>Sede</th>
                            <th>Perfil Profesional</th>
                            <th>Disponibilidad</th>
                        </tr>
                    </thead>
                    <tbody id="tablaProfesores">
                        <!-- Los datos de los profesores se cargarán aquí -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.7.0.js" integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

</html>