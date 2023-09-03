<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: login/index.php");
    exit();
}

// Destruir todas las variables de sesión
session_destroy();

// Redirigir a la página de inicio de sesión
header("Location: login/index.php");
exit();
