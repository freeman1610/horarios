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

function listar()
{
    global $conn;

    try {
        $query = "SELECT id,
                    (SELECT name FROM sede WHERE id = usuario.sede_id) AS sede,
                    (SELECT name FROM pnf WHERE id = usuario.pnf_id) AS pnf,
                    user_name,
                    user_type,
                    status
                FROM
                    usuario
                WHERE 
                    user_type IN ('admin', 'coord')";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        $query = "SELECT
                    usuario.id,
                    (SELECT name FROM sede WHERE id=profesor.sede_id) AS sede,
                    (SELECT cedula FROM profesor WHERE id= usuario.profesor_id) AS user_name,
                    (SELECT name FROM profesor WHERE id= usuario.profesor_id) AS name,
                    (SELECT last_name FROM profesor WHERE id= usuario.profesor_id) AS last_name,
                    user_type,
                    status
                FROM
                    usuario
                INNER JOIN profesor ON profesor.id = usuario.profesor_id
                WHERE
                    user_type = 'prof'
                ORDER BY sede";
        $stmt2 = $conn->prepare($query);
        $stmt2->execute();

        $res = [
            'success' => true,
            'usuarios_adm_coor' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'usuarios_prof' => $stmt2->fetchAll(PDO::FETCH_ASSOC)
        ];

        // Convertir el array en formato JSON
        $jsonResponse = json_encode($res);
        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;

        return true;
    } catch (PDOException $e) {
        echo "Error listar: " . $e->getMessage();
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

function crear($sede_id, $pnf_id, $user_type, $user_name, $password, $profesor_id)
{
    global $conn;

    try {
        if ($user_type == 'admin') {
            $query = "INSERT INTO 
                            usuario (sede_id, profesor_id, pnf_id, user_name, password, user_type)
                        VALUES
                            (:sede_id, NULL, NULL, :user_name, :password, :user_type)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':sede_id', $sede_id);
            $stmt->bindParam(':user_name', $user_name);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':user_type', $user_type);

            $stmt->execute();
            return true;
        }
        if ($user_type == 'coord') {
            $query = "INSERT INTO 
                            usuario (sede_id, profesor_id, pnf_id, user_name, password, user_type)
                        VALUES
                            (:sede_id, NULL, :pnf_id, :user_name, :password, :user_type)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':sede_id', $sede_id);
            $stmt->bindParam(':pnf_id', $pnf_id);
            $stmt->bindParam(':user_name', $user_name);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':user_type', $user_type);

            $stmt->execute();
            return true;
        }
        if ($user_type == 'prof') {
            $query = "INSERT INTO 
                    usuario (sede_id, profesor_id, pnf_id, user_name, password, user_type)
                VALUES
                    (NULL, :profesor_id, NULL, NULL, :password, :user_type)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':user_type', $user_type);
            $stmt->bindParam(':profesor_id', $profesor_id[0]);

            $stmt->execute();
            return true;
        }
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Error crear: ' . $e->getMessage()
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

function listarProfesores()
{
    global $conn;

    try {
        $query = "SELECT id, name, last_name, cedula
        FROM profesor
        WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE usuario.profesor_id = profesor.id)";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        $response = [
            'success' => true,
            'profesores' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Error listarProfesores: ' . $e->getMessage()
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}
function deshabilitar($id, $condi)
{
    global $conn;

    try {
        $query = "UPDATE usuario SET status = $condi WHERE id = $id";
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

function cambiarContra($id, $contra)
{
    global $conn;

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
function modificar($id, $name, $sede_id, $pnf_id, $tipo_user)
{
    global $conn;

    try {
        if ($tipo_user == 'admin') {
            $query = "UPDATE usuario
                    SET 
                        sede_id = $sede_id,
                        user_name = '$name'
                    WHERE id = $id";
        }
        if ($tipo_user == 'coord') {
            $query = "UPDATE usuario
                    SET 
                        sede_id = $sede_id,
                        user_name = '$name',
                        pnf_id = $pnf_id
                    WHERE id = $id";
        }

        $stmt = $conn->prepare($query);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Error modificar: ' . $e->getMessage()
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}
function eliminar($id)
{
    global $conn;

    try {
        $query = "DELETE FROM usuario WHERE id = $id";
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

function listUpdate($id, $user_tipo)
{
    global $conn;

    try {

        if ($user_tipo == 'admin') {
            $query = "SELECT user_name, sede_id FROM usuario WHERE id = $id";
        }
        if ($user_tipo == 'coord') {
            $query = "SELECT user_name, sede_id, pnf_id FROM usuario WHERE id = $id";
        }

        $stmt = $conn->prepare($query);
        $stmt->execute();

        $response = [
            'success' => true,
            'usuario' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Error listUpdateAdmin: ' . $e->getMessage()
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

switch ($_POST['tipo']) {
    case 'listar':
        listar();
        break;
    case 'listar_sedes':
        listarSedes();
        break;
    case 'listar_pnfs':
        listarPnfs();
        break;
    case 'listar_profesores':
        listarProfesores();
        break;
    case 'crear':
        crear($_POST['sede_id'], $_POST['pnf_id'], $_POST['user_type'], $_POST['user_name'], $_POST['password'], $_POST['profesor_id']);
        break;
    case 'deshabilitar':
        deshabilitar($_POST['id'], $_POST['condi']);
    case 'cambiar_contra':
        cambiarContra($_POST['id'], $_POST['contra']);
        break;
    case 'listar_update':
        listUpdate($_POST['id'], $_POST['user_tipo']);
        break;
    case 'modificar_ajax':
        modificar($_POST['id'], $_POST['name'], $_POST['sede_id'], $_POST['pnf_id'], $_POST['tipo_user']);
        break;
    case 'eliminar':
        eliminar($_POST['id']);
        break;
}
