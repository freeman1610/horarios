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
    <title>Administrador <?php echo $_SESSION['user_name'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="shortcut icon" href="../img/iconiut.png" type="image/x-icon">
</head>

<body>
    <div class="container">
        <div class="row mt-4">
            <div class="col-12">
                <img class="img-fluid" src="../img/banneriut.png" alt="UPTAI">
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <nav class="navbar navbar-expand-lg bg-body-tertiary">
                    <div class="container-fluid">
                        <a class="navbar-brand" href="#">Administrador <span class="text-success"><?php echo $_SESSION['user_name'] ?></span></a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarText">
                            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                                <li class="nav-item">
                                    <a class="nav-link" href="aulas/">Aulas</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="horario/">Horarios</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="pensum/">Pensum</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="profesiones/">Profesiones</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="profesores/">Profesores</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="secciones/">Secciones</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="usuarios/">Usuarios</a>
                                </li>
                            </ul>
                            <a href="../logout.php" class="btn btn-danger">
                                Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <div class="row d-flex d-flex justify-content-center align-items-center">
            <div class="col-12 mt-4">
                <h1>Bienvenido</h1>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>
                                    <h4 class="h4">Excepciones</h4>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tablaExcepciones"></tbody>
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