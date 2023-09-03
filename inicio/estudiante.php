<?php

if (!$_POST['tipo']) {
    header("Location: index.html");
}

require_once '../conexion.php';

// Obtener la conexión
$conexion = new Conexion();
$conn = $conexion->getConnection();


function cargarSelectSede()
{
    global $conn;

    try {
        $query = "SELECT id, name FROM sede";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        $rowCount = $stmt->rowCount();

        if ($rowCount == 0) {
            $response = array(
                'success' => false,
                'status' => 0
            );
            // Convertir el array en formato JSON
            $jsonResponse = json_encode($response);
            // Imprimir el JSON como respuesta
            header('Content-Type: application/json');
            echo $jsonResponse;
            return true;
        }

        $response = array(
            'success' => true,
            'status' => 1,
            'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
        // Convertir el array en formato JSON
        $jsonResponse = json_encode($response);
        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
        return true;
    } catch (PDOException $e) {
        $response = array(
            'success' => false,
            'message' => "Error al cargar las Secciones: " . $e->getMessage()
        );
        // Convertir el array en formato JSON
        $jsonResponse = json_encode($response);
        http_response_code(404);
        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

function cargarSelectSeccion($sede)
{
    global $conn;

    try {
        $query = "SELECT seccion.id, pnf.name AS pnf, seccion.trayecto, seccion.seccion FROM seccion
        INNER JOIN pnf ON pnf.id = seccion.pnf_id
        WHERE seccion.sede_id = :sede";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':sede', $sede);

        $stmt->execute();

        $rowCount = $stmt->rowCount();

        if ($rowCount == 0) {
            $response = array(
                'success' => false,
                'status' => 0
            );
            // Convertir el array en formato JSON
            $jsonResponse = json_encode($response);
            // Imprimir el JSON como respuesta
            header('Content-Type: application/json');
            echo $jsonResponse;
            return true;
        }

        $response = array(
            'success' => true,
            'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
        // Convertir el array en formato JSON
        $jsonResponse = json_encode($response);
        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
    } catch (PDOException $e) {
        $response = array(
            'success' => false,
            'message' => "Error al cargar las Secciones: " . $e->getMessage()
        );
        // Convertir el array en formato JSON
        $jsonResponse = json_encode($response);
        http_response_code(404);
        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

function cargarHorario($seccion_id, $tipo_horario, $semana)
{
    global $conn;

    try {
        $query1 = "SELECT
                        horario.hour,
                        pensum.codigo AS pensum_codigo,
                        aula.name AS aula_name
                    FROM
                        horario
                    INNER JOIN
                        pensum ON horario.pensum_id = pensum.id
                    INNER JOIN
                        aula ON horario.aula_id = aula.id
                    WHERE
                        seccion_id = :seccion_id 
                        AND tipo_horario = :tipo_horario
                        AND :semana BETWEEN semana_inicio AND semana_fin";

        $stmt1 = $conn->prepare($query1);
        $stmt1->bindParam(':seccion_id', $seccion_id);
        $stmt1->bindParam(':semana', $semana);
        $stmt1->bindParam(':tipo_horario', $tipo_horario);

        $stmt1->execute();

        $query2 = "SELECT
                        pensum.codigo,
                        pensum.materia,
                        pensum.uc,
                        pensum.horas
                    FROM
                        pensum
                    WHERE
                        pensum.trayecto IN (SELECT trayecto FROM seccion WHERE id = :seccion_id)
                        AND pensum.pnf_id IN (SELECT pnf_id FROM seccion WHERE id = :seccion_id)";

        $stmt2 = $conn->prepare($query2);
        $stmt2->bindParam(':seccion_id', $seccion_id);

        $stmt2->execute();

        $query4 = "SELECT
                        profesor.name,
                        profesor.last_name,
                        horario.semana_inicio,
                        horario.semana_fin,
                        pensum.codigo
                    FROM
                        horario
                    INNER JOIN
                        pensum ON horario.pensum_id = pensum.id
                    INNER JOIN
                        profesor ON horario.profesor_id = profesor.id
                    WHERE
                        horario.seccion_id = :seccion_id
                        AND horario.tipo_horario = :tipo_horario";

        $stmt4 = $conn->prepare($query4);
        $stmt4->bindParam(':seccion_id', $seccion_id);
        $stmt4->bindParam(':tipo_horario', $tipo_horario);

        $stmt4->execute();


        $query3 = "SELECT
                        pnf.name AS pnf_name,
                        seccion.trayecto,
                        seccion.seccion
                    FROM
                        seccion
                    INNER JOIN
                        pnf ON seccion.pnf_id = pnf.id
                    WHERE
                        seccion.id = :seccion_id";

        $stmt3 = $conn->prepare($query3);
        $stmt3->bindParam(':seccion_id', $seccion_id);

        $stmt3->execute();

        $query9 = "SELECT
                        id,
                        hora,
                        contenido
                    FROM
                        horas_dia
                    WHERE
                        horas_dia.turno = :tipo_horario
                    ORDER BY hora";

        $stmt9 = $conn->prepare($query9);
        $stmt9->bindParam(':tipo_horario', $tipo_horario);
        $stmt9->execute();

        $response = [
            'success' => true,
            'datos' => $stmt1->fetchAll(PDO::FETCH_ASSOC),
            'materias' => $stmt2->fetchAll(PDO::FETCH_ASSOC),
            'profesores' => $stmt4->fetchAll(PDO::FETCH_ASSOC),
            'titleH3' => $stmt3->fetchAll(PDO::FETCH_ASSOC),
            'horas_dia' => $stmt9->fetchAll(PDO::FETCH_ASSOC)
        ];
        // Convertir el array en formato JSON
        $jsonResponse = json_encode($response);
        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => "Error al obtener el horario: " . $e->getMessage()
        ];
        // Convertir el array en formato JSON
        $jsonResponse = json_encode($response);
        http_response_code(404);
        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

function cargarSemanas($seccion_id, $tipo_horario)
{
    global $conn;

    try {
        $query = "SELECT DISTINCT semana_inicio, semana_fin FROM horario WHERE seccion_id = :seccion_id AND tipo_horario = :tipo_horario";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':seccion_id', $seccion_id);
        $stmt->bindParam(':tipo_horario', $tipo_horario);

        $stmt->execute();

        // Inicializar el array que contendrá la lista de números únicos con sus conjuntos
        $numberList = [];

        // Inicializar el contador de conjuntos
        $counter = 1;

        $lista = [];

        // Recorrer los resultados y construir la lista de números únicos con sus conjuntos
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $result) {
            $start = $result['semana_inicio'];
            $end = $result['semana_fin'];

            for ($i = $start; $i <= $end; $i++) {
                // Verificar si el número ya existe en la lista antes de agregarlo
                $numberExists = false;
                foreach ($numberList as $numberItem) {
                    if ($numberItem[0] == $i) {
                        $numberExists = true;
                        break;
                    }
                }

                if (!$numberExists) {
                    $numberList[] = [$i];
                    $lista[] = ['semana' => $i, 'conjunto' => $counter];
                }
            }

            $counter++;
        }

        $response = array(
            'success' => true,
            'lista' => $lista
            // 'conjunto' => $conjunto
        );
        // Convertir el array en formato JSON
        $jsonResponse = json_encode($response);
        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
        return true;
    } catch (PDOException $e) {
        $response = array(
            'success' => false,
            'message' => "Error al cargar las Semanas: " . $e->getMessage()
        );
        // Convertir el array en formato JSON
        $jsonResponse = json_encode($response);
        http_response_code(404);
        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

switch ($_POST['tipo']) {
    case 'cargarSelectSede':
        cargarSelectSede();
        break;
    case 'cargarSelectSeccion':
        cargarSelectSeccion($_POST['sede']);
        break;
    case 'cargarHorario':
        cargarHorario($_POST['seccion_id'], $_POST['tipo_horario'], $_POST['semana']);
        break;
    case 'cargarSemanas':
        cargarSemanas($_POST['seccion_id'], $_POST['tipo_horario']);
        break;
    default:
        echo 'code  401';
        break;
}
