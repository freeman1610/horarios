<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: ../login/index.php");
    exit();
}
if ($_SESSION['user_type'] != 'admin') {
    echo 'No tienes permisos para este modulo';
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profesiones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="shortcut icon" href="../../img/iconiut.png" type="image/x-icon">
</head>

<body>
    <div class="container">
        <div class="row mt-4">
            <div class="col-12">
                <img class="img-fluid" src="../../img/banneriut.png" alt="UPTAI">
            </div>
        </div>
        <div class="row align-items-center mt-4">
            <div class="col-auto">
                <h1 class="h1">Profesiones</h1>
            </div>
            <div class="col-md-2 col-auto">
                <button class="btn btn-primary" id="btnCrear" onclick="mostrarFormulario()">Crear Profesión</button>
            </div>
            <div class="col-md-2 col-auto">
                <a class="btn btn-secondary" href="../">Regresar</a>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-4 col-12">
                <div class="form-floating mb-3">
                    <input type="text" id="searchInput" onkeyup="buscar(this)" class="form-control" autocomplete="off" placeholder="Buscar">
                    <label for="searchInput">Buscar</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table" id="dataTable">
                        <thead>
                            <tr>
                                <th>Codigo</th>
                                <th>Nombre</th>
                                <th>Profesores</th>
                                <th>Materias (Pensum)</th>
                            </tr>
                        </thead>
                        <tbody id="tablaProfesion">
                            <!-- Los datos se cargarán aquí -->
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