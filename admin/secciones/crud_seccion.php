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

require_once '../../conexion.php';

// Obtener la conexión
$conexion = new Conexion();
$conn = $conexion->getConnection();

// Agregar una sección
function addSeccion($sede_id, $pnf_id, $trayecto, $seccion)
{
    global $conn;

    try {
        $query = "INSERT INTO seccion (sede_id, pnf_id, trayecto, seccion) VALUES (:sede_id, :pnf_id, :trayecto, :seccion)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':sede_id', $sede_id);
        $stmt->bindParam(':pnf_id', $pnf_id);
        $stmt->bindParam(':trayecto', $trayecto);
        $stmt->bindParam(':seccion', $seccion);

        $stmt->execute();

        $response = array(
            'success' => true
        );
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    } catch (PDOException $e) {
        $response = array(
            'success' => false,
            'message' => 'Error al "crear" la Seccion: ' . $e->getMessage()
        );
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

// Obtener una sección por su ID
function getSeccion($id)
{
    global $conn;

    try {
        $query = "SELECT id, sede_id, pnf_id, trayecto, seccion FROM seccion WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error al obtener la sección: " . $e->getMessage();
        return false;
    }
}

// Actualizar una sección
function updateSeccion($id, $sede_id, $pnf_id, $trayecto, $seccion)
{
    global $conn;

    try {
        $query = "UPDATE seccion 
                SET pnf_id = :pnf_id,
                    sede_id = :sede_id,
                    trayecto = :trayecto, 
                    update_at = NOW() ,
                    seccion = :seccion
                WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':sede_id', $sede_id);
        $stmt->bindParam(':pnf_id', $pnf_id);
        $stmt->bindParam(':trayecto', $trayecto);
        $stmt->bindParam(':seccion', $seccion);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $response = array(
            'success' => true
        );
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    } catch (PDOException $e) {
        $response = array(
            'success' => false,
            'message' => 'Error al actualizar la Seccion: ' . $e->getMessage()
        );
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

// Eliminar una sección por su ID
function deleteSeccion($id)
{
    global $conn;

    try {
        $query = "DELETE FROM seccion WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $response = array(
            'success' => true
        );
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    } catch (PDOException $e) {
        $response = array(
            'success' => false,
            'message' => 'Error al Eliminar la Seccion: ' . $e->getMessage()
        );
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

function listarSeccion()
{
    global $conn;

    try {
        $query = "SELECT id,
                        (SELECT name FROM pnf WHERE id = seccion.pnf_id) AS pnf,
                        (SELECT name FROM sede WHERE id = seccion.sede_id) AS sede,
                        (SELECT COUNT(id) FROM horario WHERE seccion_id = seccion.id) AS horario_asignados,
                        trayecto,
                        seccion
                FROM seccion ORDER BY pnf";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error al obtener las Secciones: " . $e->getMessage();
        return false;
    }
}

function listSeccion()
{
    $Seccion = listarSeccion();

    $response = array(
        'success' => true,
        'Seccion' => $Seccion
    );
    // Convertir el array en formato JSON
    $jsonResponse = json_encode($response);
    // Imprimir el JSON como respuesta
    header('Content-Type: application/json');
    echo $jsonResponse;
}

function geT($id)
{
    $Seccion = getSeccion($id);
    $response = array('Seccion' => $Seccion);
    // Convertir el array en formato JSON

    if ($response['Seccion'] == 404) {
        http_response_code(404);
        $response = array(
            'success' => false,
            'menssage' => 'No Found'
        );
    } else {
        http_response_code(200);
        $response = array(
            'success' => false,
            'Seccion' => $Seccion
        );
    }
    $jsonResponse = json_encode($response);

    // Imprimir el JSON como respuesta
    header('Content-Type: application/json');
    echo $jsonResponse;
}

function listarSedes()
{
    global $conn;

    try {
        $query = "SELECT id, name FROM sede";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        $response = [
            'success' => true,
            'sedes' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Error listarSedes: ' . $e->getMessage()
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

function listarPnfs()
{
    global $conn;

    try {
        $query = "SELECT id, name FROM pnf";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        $response = [
            'success' => true,
            'pnfs' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Error listarPnfs: ' . $e->getMessage()
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

switch ($_POST['tipo']) {
    case 'crear':
        addSeccion($_POST['sede_id'], $_POST['pnf'], $_POST['trayecto'], $_POST['seccion']);
        break;
    case 'actualizar':
        updateSeccion($_POST['id'], $_POST['sede_id'], $_POST['sede_id'], $_POST['trayecto'], $_POST['seccion']);
        break;
    case 'eliminar':
        deleteSeccion($_POST['id']);
        break;
    case 'listar':
        listSeccion();
        break;
    case 'listar_uno':
        geT($_POST['id']);
        break;
    case 'listar_sedes':
        listarSedes();
        break;
    case 'listar_pnfs':
        listarPnfs();
        break;
    default:
        echo '401';
        break;
}
