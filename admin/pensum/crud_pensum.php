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

// Agregar un pensum
function addPensum($codigo, $materia, $pnf_id, $trayecto, $trimestre, $uc, $horas)
{
    global $conn;

    try {
        $query = "INSERT INTO pensum (codigo, materia, pnf_id, trayecto, trimestre, uc, horas) VALUES (:codigo, :materia, :pnf_id, :trayecto, :trimestre, :uc, :horas)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':materia', $materia);
        $stmt->bindParam(':pnf_id', $pnf_id);
        $stmt->bindParam(':trayecto', $trayecto);
        $stmt->bindParam(':trimestre', $trimestre);
        $stmt->bindParam(':uc', $uc);
        $stmt->bindParam(':horas', $horas);

        $stmt->execute();

        $response = [
            'success' => true
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Error al "crear" el Pensum: ' . $e->getMessage()
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

// Obtener un pensum por su ID
function getPensum($id)
{
    global $conn;

    try {
        $query = "SELECT pensum.id,
                        pensum.codigo,
                        pensum.materia,
                        pensum.pnf_id,
                        pensum.trayecto,
                        pensum.trimestre,
                        pensum.uc,
                        pensum.horas
                    FROM pensum
                    INNER JOIN 
                        pnf ON pnf.id = pensum.pnf_id
                    WHERE pensum.id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $rowCount = $stmt->rowCount();

        if ($rowCount == 0) {
            return 404;
        } else {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        echo "Error al obtener el pensum: " . $e->getMessage();
        return false;
    }
}

// Actualizar un pensum
function updatePensum($id, $codigo, $materia, $pnf_id, $trayecto, $trimestre, $uc, $horas,)
{
    global $conn;

    try {
        $query = "UPDATE pensum 
                    SET
                        codigo = :codigo,
                        materia = :materia,
                        pnf_id = :pnf_id,
                        trayecto = :trayecto,
                        trimestre = :trimestre,
                        uc = :uc,
                        horas = :horas,
                        update_at = NOW()
                    WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':materia', $materia);
        $stmt->bindParam(':pnf_id', $pnf_id);
        $stmt->bindParam(':trayecto', $trayecto);
        $stmt->bindParam(':trimestre', $trimestre);
        $stmt->bindParam(':uc', $uc);
        $stmt->bindParam(':horas', $horas);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $response = [
            'success' => true
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Error al actualizar el Pensum: ' . $e->getMessage()
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

// Eliminar un pensum por su ID
function deletePensum($id)
{
    global $conn;

    try {
        $query = "DELETE FROM pensum WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $response = [
            'success' => true
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Error al Eliminar el Pensum: ' . $e->getMessage()
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

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
                GROUP BY 
                    pensum.id, pensum.codigo, pensum.materia, pnf.name, pensum.trayecto, pensum.trimestre, pensum.uc, pensum.horas
                ORDER BY 
                    pensum.id";
        $stmt = $conn->prepare($query);
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

function geT($id)
{
    $pensum = getpensum($id);
    $response = [
        'pensum' => $pensum
    ];
    // Convertir el array en formato JSON

    if ($response['pensum'] == 404) {
        http_response_code(404);
        $response = [
            'success' => false,
            'menssage' => 'No Found'
        ];
    } else {
        http_response_code(200);
        $response = [
            'success' => false,
            'pensum' => $pensum
        ];
    }
    $jsonResponse = json_encode($response);

    // Imprimir el JSON como respuesta
    header('Content-Type: application/json');
    echo $jsonResponse;
}

function generarSelectPNF()
{
    global $conn;

    $query = "SELECT id, name FROM pnf ORDER BY name";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $response = [
        'success' => true,
        'pnf' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ];
    $jsonResponse = json_encode($response);

    // Imprimir el JSON como respuesta
    header('Content-Type: application/json');
    echo $jsonResponse;
    return true;
}

function listarAsignarProfesion($id, $update)
{
    global $conn;

    try {
        if ($update == 'true') {
            $query = "SELECT id, name FROM profesion 
            WHERE id NOT IN (SELECT profesion_id FROM pensum_profesion WHERE pensum_id = :id)
            ORDER BY id ASC, name ASC";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $profesiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $query = "SELECT id, name FROM profesion 
            WHERE id IN (SELECT profesion_id FROM pensum_profesion WHERE pensum_id = :id)
            ORDER BY id ASC, name ASC";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $profesiones_asignadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = [
                'success' => true,
                'profesiones' => $profesiones,
                'profesiones_asignadas' => $profesiones_asignadas
            ];
            $jsonResponse = json_encode($response);
            header('Content-Type: application/json');
            echo $jsonResponse;
        }
        if ($update == 'false') {
            $query = "SELECT id, name FROM profesion ORDER BY id ASC, name ASC";
            $stmt = $conn->prepare($query);
            $stmt->execute();

            $response = [
                'success' => true,
                'profesiones' => $stmt->fetchAll(PDO::FETCH_ASSOC)
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

function guardarPensumProfesion($pensum_id, $datos)
{
    global $conn;

    try {
        $query = "SELECT profesion_id FROM pensum_profesion WHERE pensum_id = :pensum_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':pensum_id', $pensum_id);
        $stmt->execute();

        $rowCount = $stmt->rowCount();

        if ($rowCount == 0) {
            foreach ($datos as $item) {
                $query = "INSERT INTO pensum_profesion (pensum_id, profesion_id) 
                VALUES ($pensum_id, " . $item['id'] . ")";
                $stmt = $conn->prepare($query);
                $stmt->execute();
            }
        } else {
            $query = "DELETE FROM pensum_profesion WHERE pensum_id = $pensum_id;";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            foreach ($datos as $item) {
                $query = "INSERT INTO pensum_profesion (pensum_id, profesion_id) 
                VALUES ($pensum_id, " . $item['id'] . ")";
                $stmt = $conn->prepare($query);
                $stmt->execute();
            }
        }

        $response = [
            'success' => true
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
        return true;
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Error guardarPensumProfesion: ' . $e->getMessage()
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}


switch ($_POST['tipo']) {
    case 'crear':
        addPensum($_POST['codigo'], $_POST['materia'], $_POST['pnf_id'], $_POST['trayecto'], $_POST['trimestre'], $_POST['uc'], $_POST['horas']);
        break;
    case 'actualizar':
        updatePensum($_POST['id'], $_POST['codigo'], $_POST['materia'], $_POST['pnf_id'], $_POST['trayecto'], $_POST['trimestre'], $_POST['uc'], $_POST['horas']);
        break;
    case 'eliminar':
        deletePensum($_POST['id']);
        break;
    case 'listar':
        listPensum();
        break;
    case 'listar_uno':
        geT($_POST['id']);
        break;
    case 'generarSelectPNF':
        generarSelectPNF();
        break;
    case 'listar_asignar_profesion':
        listarAsignarProfesion($_POST['id'], $_POST['update']);
        break;
    case 'guardarPensumProfesion':
        guardarPensumProfesion($_POST['id_pensum'], $_POST['datos']);
        break;
    default:
        echo '401';
        break;
}
