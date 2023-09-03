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
require_once '../conexion.php';
// Obtener la conexión
$conexion = new Conexion();
$conn = $conexion->getConnection();

function cargarExcepciones()
{
    global $conn;
    try {
        $query = "SELECT
                    (SELECT user_name FROM usuario WHERE id = excepciones.user_id) AS user_name,
                    user_id,
                    (SELECT trayecto FROM seccion WHERE id IN (SELECT seccion_id FROM horario WHERE id = excepciones.horario_id)) AS seccion_trayecto,
                    (SELECT seccion FROM seccion WHERE id IN (SELECT seccion_id FROM horario WHERE id = excepciones.horario_id)) AS seccion,
                    (SELECT name FROM pnf WHERE id IN (SELECT pnf_id FROM seccion WHERE id IN (SELECT seccion_id FROM horario WHERE id = excepciones.horario_id))) AS seccion_pnf,
                    (SELECT name FROM sede WHERE id IN (SELECT sede_id FROM seccion WHERE id IN (SELECT seccion_id FROM horario WHERE id = excepciones.horario_id))) AS seccion_sede,

                    (SELECT name FROM profesor WHERE id = excepciones.profesor_id) AS prof_name,
                    (SELECT last_name FROM profesor WHERE id = excepciones.profesor_id) AS prof_last_name,
                    (SELECT cedula FROM profesor WHERE id = excepciones.profesor_id) AS cedula,
                    profesor_id,
                    (SELECT contenido FROM horas_dia WHERE id = excepciones.hora_dia_id) AS hora_text,
                    dia_text,
                    (SELECT codigo FROM pensum WHERE id = excepciones.pensum_id) AS codigo,
                    (SELECT materia FROM pensum WHERE id = excepciones.pensum_id) AS materia,
                    pensum_id,
                    DATE_FORMAT(created_at, '%d/%m/%Y %h:%i %p') AS fecha
                     
                FROM excepciones";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $response = [
            'success' => true,
            'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
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

switch ($_POST['tipo']) {
    case 'cargarExcepciones':
        cargarExcepciones();
        break;
}
