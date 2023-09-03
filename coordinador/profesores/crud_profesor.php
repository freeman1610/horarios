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

// Listar Profesores

function listarProfesores()
{
    global $conn;
    $sede_id = $_SESSION['sede_id'];
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
                WHERE
                    sede.id = $sede_id
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

function listarAsignarProfesion($id, $update)
{
    global $conn;

    try {
        if ($update == 'true') {
            $query = "SELECT id, name FROM profesion 
            WHERE id IN (SELECT profesion_id FROM perfil_profesional WHERE profesor_id = :id)
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
        listProf();
        break;
    case 'listar_asignar_profesion':
        listarAsignarProfesion($_POST['id_profesor'], $_POST['update']);
        break;
    default:
        echo '401';
        break;
}
