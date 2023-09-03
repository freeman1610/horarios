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

// Crear un profesor
function createProfesor($name, $last_name, $cedula, $sede_id)
{
    global $conn;

    try {
        $query = "INSERT INTO profesor (sede_id, name, last_name, cedula, created_at, update_at) VALUES (:sede_id, :name, :last_name, :cedula, NOW(), NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':sede_id', $sede_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':cedula', $cedula);
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
            'message' => 'Error al "crear" el profesor: ' . $e->getMessage()
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

// Obtener información de un profesor por su ID
function getProfesor($id)
{
    global $conn;

    try {
        $query = "SELECT profesor.id, profesor.name, profesor.last_name, profesor.cedula, sede.id AS sede_id FROM profesor
        INNER JOIN sede ON sede.id = profesor.sede_id
        WHERE profesor.id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error al obtener el profesor: " . $e->getMessage();
        return false;
    }
}

// Listar Profesores

function listarProfesores()
{
    global $conn;

    try {
        $query = "SELECT 
                    profesor.id,
                    profesor.name,
                    sede.name AS sede_name,
                    profesor.last_name,
                    profesor.cedula,
                    COUNT(perfil_profesional.id) AS total_perfiles,
                    (SELECT COUNT(id) FROM horario WHERE profesor_id = profesor.id) AS horario_asignados,
                    (SELECT COUNT(id) FROM profesor_disponibilidad WHERE profesor_id = profesor.id) AS disponibilidades_creadas,
                    (SELECT COUNT(id) FROM profesor_disponibilidad_status WHERE profesor_id = profesor.id) AS disponibilidad_status_creada,
                    (SELECT status FROM profesor_disponibilidad_status WHERE profesor_id = profesor.id) AS disponibilidad_status
                FROM 
                    profesor
                INNER JOIN
                    sede ON sede.id = profesor.sede_id
                LEFT JOIN
                    perfil_profesional ON perfil_profesional.profesor_id = profesor.id
                GROUP BY 
                    profesor.id, profesor.name, profesor.last_name, profesor.cedula
                ORDER BY 
                    sede.name ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error al obtener los profesores: " . $e->getMessage();
        return false;
    }
}


// Actualizar información de un profesor
function updateProfesor($id, $name, $last_name, $cedula, $sede_id)
{
    global $conn;

    try {
        $query = "UPDATE profesor SET name = :name, last_name = :last_name, cedula = :cedula, sede_id = :sede_id, update_at = NOW()
                  WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':cedula', $cedula);
        $stmt->bindParam(':sede_id', $sede_id);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $response = [
            'success' => true
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    } catch (PDOException $e) {
        $response = array(
            'success' => false,
            'message' => 'Error al actualizar el profesor: ' . $e->getMessage()
        );
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

// Eliminar un profesor por su ID
function deleteProfesor($id)
{
    global $conn;

    try {
        $query = "DELETE FROM profesor WHERE id = :id";
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
            'message' => 'Error al Eliminar el profesor: ' . $e->getMessage()
        );
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

function listProf()
{
    $profesores = listarProfesores();


    $response = [
        'success' => true,
        'profesores' => $profesores
    ];
    // Convertir el array en formato JSON
    $jsonResponse = json_encode($response);

    // Imprimir el JSON como respuesta
    header('Content-Type: application/json');
    echo $jsonResponse;
}
function geT($id)
{
    $profesor = getProfesor($id);

    $response = [
        'success' => true,
        'profesor' => $profesor
    ];

    $jsonResponse = json_encode($response);
    http_response_code(200);
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
function listarAsignarProfesion($id, $update)
{
    global $conn;

    try {
        if ($update == 'true') {
            $query = "SELECT id, name FROM profesion 
            WHERE id NOT IN (SELECT profesion_id FROM perfil_profesional WHERE profesor_id = :id)
            ORDER BY id ASC, name ASC";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $profesiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $query = "SELECT id, name FROM profesion 
            WHERE id IN (SELECT profesion_id FROM perfil_profesional WHERE profesor_id = :id)
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

function guardarPerfilProfesional($profesor_id, $datos)
{
    global $conn;

    try {
        $query = "SELECT profesion_id FROM perfil_profesional WHERE profesor_id = :profesor_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':profesor_id', $profesor_id);
        $stmt->execute();

        $rowCount = $stmt->rowCount();

        if ($rowCount == 0) {
            foreach ($datos as $item) {
                $query = "INSERT INTO perfil_profesional (profesor_id, profesion_id) 
                VALUES ($profesor_id, " . $item['id'] . ")";
                $stmt = $conn->prepare($query);
                $stmt->execute();
            }
        } else {
            $query = "DELETE FROM perfil_profesional WHERE profesor_id = $profesor_id;";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            foreach ($datos as $item) {
                $query = "INSERT INTO perfil_profesional (profesor_id, profesion_id) 
                VALUES ($profesor_id, " . $item['id'] . ")";
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
        createProfesor($_POST['name'], $_POST['last_name'], $_POST['cedula'], $_POST['sede_id']);
        break;
    case 'actualizar':
        updateProfesor($_POST['id'], $_POST['name'], $_POST['last_name'], $_POST['cedula'], $_POST['sede_id']);
        break;
    case 'eliminar':
        deleteProfesor($_POST['id']);
        break;
    case 'listar':
        listProf();
        break;
    case 'listar_uno':
        geT($_POST['id']);
        break;
    case 'listar_sedes':
        listarSedes();
        break;
    case 'listar_asignar_profesion':
        listarAsignarProfesion($_POST['id_profesor'], $_POST['update']);
        break;
    case 'guardarPerfilProfesional':
        guardarPerfilProfesional($_POST['profesor_id'], $_POST['datos']);
        break;
    default:
        echo '401';
        break;
}
