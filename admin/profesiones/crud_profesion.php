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


function listarProfesiones()
{
    global $conn;

    try {
        $query = "SELECT
                    profesion.id,
                    profesion.name,
                    (SELECT COUNT(id) FROM perfil_profesional WHERE profesion_id = profesion.id) AS total_profesores,
                    (SELECT COUNT(id) FROM pensum_profesion WHERE profesion_id = profesion.id) AS total_pensum
                FROM profesion";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        $response = [
            'success' => true,
            'profesiones' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];

        $jsonResponse = json_encode($response);

        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
        return true;
    } catch (PDOException $e) {
        echo "Error listarProfesiones: " . $e->getMessage();
        return false;
    }
}
function listarProfesores($id_profesion)
{
    global $conn;

    try {
        $query = "SELECT
                    name,
                    last_name,
                    cedula
                FROM profesor
                WHERE
                    id IN (SELECT profesor_id FROM perfil_profesional WHERE profesion_id = :id_profesion)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id_profesion', $id_profesion);
        $stmt->execute();

        $response = [
            'success' => false,
            'profesores' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];

        $jsonResponse = json_encode($response);

        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
    } catch (PDOException $e) {
        echo "Error listarProfesores: " . $e->getMessage();
        return false;
    }
}
function listarPensums($id_profesion)
{
    global $conn;

    try {
        $query = "SELECT
                    codigo,
                    materia,
                    (SELECT name FROM pnf WHERE id = pensum.pnf_id) AS pnf_name,
                    trayecto
                FROM pensum
                WHERE
                    id IN (SELECT pensum_id FROM pensum_profesion WHERE profesion_id = :id_profesion)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id_profesion', $id_profesion);
        $stmt->execute();

        $response = [
            'success' => true,
            'pensums' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];

        $jsonResponse = json_encode($response);

        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
        return true;
    } catch (PDOException $e) {
        echo "Error listarProfesores: " . $e->getMessage();
        return false;
    }
}

function crear($codigo, $name)
{
    global $conn;

    try {
        $query = "INSERT INTO profesion (id, name) VALUES (:codigo, :name)";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':name', $name);

        $stmt->execute();
        $response = [
            'success' => true,
            'pensums' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];

        $jsonResponse = json_encode($response);

        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
        return true;
    } catch (PDOException $e) {
        echo "Error crear: " . $e->getMessage();
        return false;
    }
}

function codigosDisponibles()
{
    global $conn;

    try {
        $query = "SELECT
                    CASE
                        WHEN id BETWEEN 1 AND 199 THEN 'tsu'
                        WHEN id BETWEEN 200 AND 399 THEN 'ingeniero'
                        WHEN id BETWEEN 400 AND 599 THEN 'licenciado'
                    END AS `profesion`,
                    MAX(id) AS ultimo_codigo
                FROM profesion
                WHERE id BETWEEN 1 AND 599
                GROUP BY `profesion`";

        $stmt = $conn->prepare($query);

        $stmt->execute();
        $response = [
            'success' => true,
            'codigos_disponibles' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];

        $jsonResponse = json_encode($response);

        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
        return true;
    } catch (PDOException $e) {
        echo "Error codigosDisponibles: " . $e->getMessage();
        return false;
    }
}

function mostrarProfesion($id_profesion)
{
    global $conn;

    try {
        $query = "SELECT id AS codigo, name FROM profesion WHERE id = :id_profesion";

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':id_profesion', $id_profesion);

        $stmt->execute();
        $response = [
            'success' => true,
            'profesion' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];

        $jsonResponse = json_encode($response);

        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
        return true;
    } catch (PDOException $e) {
        echo "Error mostrarProfesion: " . $e->getMessage();
        return false;
    }
}

function actualizar($id_profesion, $name, $codigo)
{
    global $conn;

    try {
        $query = "UPDATE 
                    profesion 
                SET 
                    id = :codigo,
                    name = :name
                WHERE 
                    id = :id_profesion";

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':id_profesion', $id_profesion);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':codigo', $codigo);

        $stmt->execute();

        return true;
    } catch (PDOException $e) {
        echo "Error actualizar: " . $e->getMessage();
        return false;
    }
}

function eliminar($id_profesion)
{
    global $conn;

    try {
        $query = "DELETE FROM profesion WHERE id = :id_profesion";

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':id_profesion', $id_profesion);

        $stmt->execute();

        return true;
    } catch (PDOException $e) {
        echo "Error actualizar: " . $e->getMessage();
        return false;
    }
}

switch ($_POST['tipo']) {
    case 'listarProfesiones':
        listarProfesiones();
        break;
    case 'listar_profesores':
        listarProfesores($_POST['id_profesion']);
        break;
    case 'listar_pensums':
        listarPensums($_POST['id_profesion']);
        break;
    case 'mostrar_codigos_disponibles':
        codigosDisponibles();
        break;
    case 'crear':
        crear($_POST['codigo'], $_POST['name']);
        break;
    case 'form_actualizar':
        mostrarProfesion($_POST['id_profesion']);
        break;
    case 'actualizar_ajax':
        actualizar($_POST['id_profesion'], $_POST['name'], $_POST['codigo']);
        break;
    case 'eliminar':
        eliminar($_POST['id_profesion']);
        break;
}
