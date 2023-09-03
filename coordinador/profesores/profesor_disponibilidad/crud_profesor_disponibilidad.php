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

require_once '../../../conexion.php';

// Obtener la conexión
$conexion = new Conexion();
$conn = $conexion->getConnection();

// Agregar disponibilidad de un profesor
function addProfesorDisponibilidad($profesorId, $hour, $disponibilidad, $tipo_horario)
{
    global $conn;

    try {
        $query = "INSERT INTO profesor_disponibilidad (profesor_id, hour, disponibilidad, tipo_horario) 
                  VALUES (:profesor_id, :hour, :disponibilidad, :tipo_horario)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':profesor_id', $profesorId);
        $stmt->bindParam(':hour', $hour);
        $stmt->bindParam(':disponibilidad', $disponibilidad);
        $stmt->bindParam(':tipo_horario', $tipo_horario);

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
            'message' => 'Error al agregar la disponibilidad del profesor: ' . $e->getMessage()
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

// Obtener disponibilidad de un profesor por su ID
function getProfesorDisponibilidad($profesor_id, $tipo_horario)
{
    global $conn;

    try {
        $query1 = "SELECT disponibilidad, hour FROM profesor_disponibilidad
        WHERE profesor_id = :profesor_id AND tipo_horario = :tipo_horario";
        $stmt1 = $conn->prepare($query1);
        $stmt1->bindParam(':profesor_id', $profesor_id);
        $stmt1->bindParam(':tipo_horario', $tipo_horario);

        $stmt1->execute();

        $query2 = "SELECT horario.semana_inicio, horario.semana_fin, horario.hour AS posicion,
        pensum.materia AS pensum_name, aula.name AS aula_name,
        seccion.trayecto AS trayecto_seccion,
        seccion.seccion AS seccion_seccion,
        (SELECT name FROM pnf WHERE id = seccion.pnf_id) AS carrera
        FROM horario
        INNER JOIN pensum ON horario.pensum_id = pensum.id
        INNER JOIN aula ON horario.aula_id = aula.id
        INNER JOIN seccion ON horario.seccion_id = seccion.id
        WHERE horario.profesor_id = :profesor_id
        ORDER BY horario.semana_inicio ASC";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bindParam(':profesor_id', $profesor_id);

        $stmt2->execute();

        $response = [
            'success' => true,
            'data_profesor_disponibilidad' => $stmt1->fetchAll(PDO::FETCH_ASSOC),
            'data_hora' => $stmt2->fetchAll(PDO::FETCH_ASSOC),
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Error al obtener la disponibilidad del profesor: ' . $e->getMessage()
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

// Actualizar disponibilidad de un profesor
function updateProfesorDisponibilidad($profesorId, $hour, $disponibilidad, $tipoH)
{
    global $conn;

    try {
        $query = "UPDATE profesor_disponibilidad 
                  SET hour = :hour, disponibilidad = :disponibilidad, update_at = NOW(), tipo_horario = :tipo_horario 
                  WHERE profesor_id = :profesor_id AND hour = :hour 
                  AND tipo_horario = :tipo_horario";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':hour', $hour);
        $stmt->bindParam(':disponibilidad', $disponibilidad);
        $stmt->bindParam(':profesor_id', $profesorId);
        $stmt->bindParam(':tipo_horario', $tipoH);

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
            'message' => 'Error al actualizar la disponibilidad del profesor: ' . $e->getMessage()
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

// Eliminar disponibilidad de un profesor por su ID
function deleteProfesorDisponibilidad($id)
{
    global $conn;

    try {
        $query = "DELETE FROM profesor_disponibilidad WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return true;
    } catch (PDOException $e) {
        echo "Error al eliminar la disponibilidad del profesor: " . $e->getMessage();
        return false;
    }
}
function geT($id)
{
    global $conn;

    try {
        $query = "SELECT name, last_name FROM profesor WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $rowCount = $stmt->rowCount();

        $res = 200;

        if ($rowCount == 0) {
            $res = 404;
        } else {
            $query3 = "SELECT status FROM profesor_disponibilidad_status
            WHERE profesor_id = :profesor_id";

            $stmt3 = $conn->prepare($query3);
            $stmt3->bindParam(':profesor_id', $id);

            $stmt3->execute();

            $rowCount = $stmt3->rowCount();

            $status = 0;

            if ($rowCount == 0) {
                $status = 0;
            } else {
                $statusQuery = $stmt3->fetch(PDO::FETCH_ASSOC);
                if ($statusQuery["status"]) {
                    $status = 1;
                } else {
                    $status = 2;
                }
            }
            // Crear vista al cargar la pagina (Cargado, Sin Cargar, Sin Crear)
        }
        // Convertir el array en formato JSON
        if ($res == 404) {
            http_response_code(404);
            $response = [
                'success' => false,
                'menssage' => 'No Found'
            ];
        } else {
            http_response_code(200);
            $response = [
                'success' => true,
                'datos_profe' => $stmt->fetch(PDO::FETCH_ASSOC),
                'status' => $status
            ];
        }
        $jsonResponse = json_encode($response);

        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
    } catch (PDOException $e) {
        echo "Error al obtener el profesor: " . $e->getMessage();
        return false;
    }
}

function verificar($profesorId, $posicion, $disponibilidad, $tipoH)
{
    global $conn;

    try {
        $query = "SELECT id FROM profesor_disponibilidad WHERE 
        profesor_id = :id AND 
        tipo_horario = :tipoh AND
        hour = :posicion";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $profesorId);
        $stmt->bindParam(':tipoh', $tipoH);
        $stmt->bindParam(':posicion', $posicion);

        $stmt->execute();
        $rowCount = $stmt->rowCount();

        // si el rowCount es cero, se crea la disponibilidad, de lo contrario
        // se actualiza

        if ($rowCount == 0) {
            addProfesorDisponibilidad($profesorId, $posicion, $disponibilidad, $tipoH);
        } else {
            updateProfesorDisponibilidad($profesorId, $posicion, $disponibilidad, $tipoH);
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

function guardarCargaHoraria($profesor_id)
{
    global $conn;

    try {
        $query1 = "SELECT profesor_id FROM profesor_disponibilidad_status WHERE 
                    profesor_id = :profesor_id";
        $stmt1 = $conn->prepare($query1);
        $stmt1->bindParam(':profesor_id', $profesor_id);

        $stmt1->execute();
        $rowCount = $stmt1->rowCount();

        if ($rowCount == 0) {
            $query2 = "INSERT INTO profesor_disponibilidad_status(profesor_id, status, created_at, updated_at) VALUES (:profesor_id, true, NOW(), NOW())";
            $stmt2 = $conn->prepare($query2);
            $stmt2->bindParam(':profesor_id', $profesor_id);

            $stmt2->execute();
            $response = [
                'success' => true,
                'status' => 1
            ];
            $jsonResponse = json_encode($response);
            header('Content-Type: application/json');
            echo $jsonResponse;
        } else {
            $query3 = "UPDATE profesor_disponibilidad_status
                        SET status = true, updated_at = NOW()
                        WHERE profesor_id = :profesor_id;";
            $stmt3 = $conn->prepare($query3);
            $stmt3->bindParam(':profesor_id', $profesor_id);

            $stmt3->execute();
            $response = [
                'success' => true,
                'status' => 1
            ];
            $jsonResponse = json_encode($response);
            header('Content-Type: application/json');
            echo $jsonResponse;
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

function desbloquearCargaHoraria($profesor_id)
{
    global $conn;

    try {
        $query3 = "UPDATE profesor_disponibilidad_status
                    SET status = false, updated_at = NOW()
                    WHERE profesor_id = :profesor_id;";
        $stmt3 = $conn->prepare($query3);
        $stmt3->bindParam(':profesor_id', $profesor_id);

        $stmt3->execute();
        $response = [
            'success' => true,
            'status' => 0
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}
function cargarHorasDia($tipo_h)
{
    global $conn;

    try {
        $query9 = "SELECT
                        hora,
                        contenido
                    FROM
                        horas_dia
                    WHERE
                        horas_dia.turno = :tipo_horario
                    ORDER BY hora";

        $stmt9 = $conn->prepare($query9);
        $stmt9->bindParam(':tipo_horario', $tipo_h);
        $stmt9->execute();
        $response = [
            'success' => true,
            'horas_dia' => $stmt9->fetchAll(PDO::FETCH_ASSOC)
        ];
        $jsonResponse = json_encode($response);
        header('Content-Type: application/json');
        echo $jsonResponse;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}
switch ($_POST['tipo']) {
    case 'mostrar_name':
        geT($_POST['id']);
        break;

    case 'verificar':
        $datos = $_POST['datos'];
        verificar($_POST['id'], $datos['posicion'], $datos['disponibilidad'], $datos['tipoh']);
        break;

    case 'cargar_tabla':
        getProfesorDisponibilidad($_POST['id'], $_POST['horario']);
        break;
    case 'guardar_carga_horaria':
        guardarCargaHoraria($_POST['profesor_id']);
        break;
    case 'desbloquear_carga_horaria':
        desbloquearCargaHoraria($_POST['profesor_id']);
        break;
    case 'cargar_horas_dia':
        cargarHorasDia($_POST['tipo_h']);
        break;
}
