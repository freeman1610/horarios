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

// Agregar un aula
function addAula($name, $capacity, $codigo, $sede_id)
{
    global $conn;

    try {
        $query = "INSERT INTO aula (codigo, sede_id, name, capacity) VALUES (:codigo, :sede_id, :name, :capacity)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':sede_id', $sede_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':capacity', $capacity);
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
            'message' => 'Error al "crear" el Aula: ' . $e->getMessage()
        );
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

// Obtener un aula por su ID
function getAula($id)
{
    global $conn;

    try {
        $query = "SELECT id, name, capacity, codigo, sede_id FROM aula WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error al obtener el aula: " . $e->getMessage();
        return false;
    }
}

// Actualizar un aula
function updateAula($id, $name, $capacity, $codigo, $sede_id)
{
    global $conn;

    try {
        $query = "UPDATE aula SET name = :name, capacity = :capacity, codigo = :codigo, sede_id = :sede_id update_at = NOW() WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':sede_id', $sede_id);
        $stmt->bindParam(':capacity', $capacity);
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
            'message' => 'Error al actualizar el Aula: ' . $e->getMessage()
        );
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

// Eliminar un aula por su ID
function deleteAula($id)
{
    global $conn;

    try {
        $query = "DELETE FROM aula WHERE id = :id";
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
            'message' => 'Error al Eliminar el Aula: ' . $e->getMessage()
        );
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

function listarAula()
{
    global $conn;

    try {
        $query = "SELECT aula.id, sede.name AS sede_name, aula.codigo, aula.name, aula.capacity FROM aula
        INNER JOIN sede ON sede.id = aula.sede_id
        ORDER BY aula.name";
        $stmt = $conn->prepare($query);
        $stmt->execute();


        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error al obtener las Aula: " . $e->getMessage();
        return false;
    }
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
function listAula()
{
    $Aula = listarAula();

    $response = array(
        'success' => true,
        'Aula' => $Aula
    );
    // Convertir el array en formato JSON
    $jsonResponse = json_encode($response);

    // Imprimir el JSON como respuesta
    header('Content-Type: application/json');
    echo $jsonResponse;
}

function geT($id)
{
    $Aula = getAula($id);
    $response = array('Aula' => $Aula);
    // Convertir el array en formato JSON

    if ($response['Aula'] == 404) {
        http_response_code(404);
        $response = array(
            'success' => false,
            'menssage' => 'No Found'
        );
    } else {
        http_response_code(200);
        $response = array(
            'success' => false,
            'Aula' => $Aula
        );
    }
    $jsonResponse = json_encode($response);

    // Imprimir el JSON como respuesta
    header('Content-Type: application/json');
    echo $jsonResponse;
}



switch ($_POST['tipo']) {
    case 'crear':
        addAula($_POST['name'], $_POST['capacity'], $_POST['codigo'], $_POST['sede_id']);
        break;
    case 'actualizar':
        updateAula($_POST['id'], $_POST['name'], $_POST['capacity'], $_POST['codigo'], $_POST['sede_id']);
        break;
    case 'eliminar':
        deleteAula($_POST['id']);
        break;
    case 'listar':
        listAula();
        break;
    case 'listar_uno':
        geT($_POST['id']);
        break;
    case 'listar_sedes':
        listarSedes();
        break;
    default:
        echo '401';
        break;
}
