<?php

session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: ../login/index.php");
    exit();
}
if ($_SESSION['user_type'] != 'prof') {
    echo 'No tienes permisos para este modulo';
    exit();
}
require_once '../conexion.php';
// Obtener la conexión
$conexion = new Conexion();
$conn = $conexion->getConnection();

function cambiarContra($contra)
{
    global $conn;
    $id = $_SESSION['user_id'];
    try {
        $query = "UPDATE usuario SET password = '$contra' WHERE id = $id";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Error deshabilitar: ' . $e->getMessage()
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

if ($_POST['tipo'] == 'cambiar_contra') {
    cambiarContra($_POST['contra']);
}
