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
require_once '../../conexion.php';

// Obtener la conexión
$conexion = new Conexion();
$conn = $conexion->getConnection();

function listarPensum()
{
    global $conn;
    try {
        $query = "SELECT 
                    pensum.id,
                    COUNT(pensum_profesion.id) AS registro_profesion,
                    pensum.codigo,
                    pensum.materia,
                    pnf.name AS name_pnf,
                    pensum.trayecto,
                    pensum.trimestre,
                    pensum.uc,
                    pensum.horas 
                FROM 
                    pensum
                INNER JOIN 
                    pnf ON pnf.id = pensum.pnf_id
                LEFT JOIN
                    pensum_profesion ON pensum_profesion.pensum_id = pensum.id
                WHERE
                    pensum.pnf_id = :pnf_id
                GROUP BY 
                    pensum.id, pensum.codigo, pensum.materia, pnf.name, pensum.trayecto, pensum.trimestre, pensum.uc, pensum.horas
                ORDER BY 
                    pensum.id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':pnf_id', $_SESSION['pnf_id']);
        $stmt->execute();
        $rowCount = $stmt->rowCount();

        if ($rowCount == 0) {
            return 404;
        } else {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        echo "Error al obtener los Pensum: " . $e->getMessage();
        return false;
    }
}

function listPensum()
{
    $Pensum = listarPensum();


    $response = [
        'success' => true,
        'Pensum' => $Pensum
    ];
    // Convertir el array en formato JSON
    $jsonResponse = json_encode($response);

    if ($response['Pensum'] == 404) {
        http_response_code(404);
    }

    // Imprimir el JSON como respuesta
    header('Content-Type: application/json');
    echo $jsonResponse;
}

function listarAsignarProfesion($id, $update)
{
    global $conn;

    try {
        if ($update == 'true') {
            $query = "SELECT id, name FROM profesion 
            WHERE id IN (SELECT profesion_id FROM pensum_profesion WHERE pensum_id = :id)
            ORDER BY id ASC, name ASC";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $profesiones_asignadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = [
                'success' => true,
                'profesiones_asignadas' => $profesiones_asignadas
            ];
            $jsonResponse = json_encode($response);
            header('Content-Type: application/json');
            echo $jsonResponse;
        }
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Error listarAsignarProfesion: ' . $e->getMessage()
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}


switch ($_POST['tipo']) {
    case 'listar':
        listPensum();
        break;
    case 'listar_asignar_profesion':
        listarAsignarProfesion($_POST['id'], $_POST['update']);
        break;
    default:
        echo '401';
        break;
}
