<?php

session_start();

if (!isset($_SESSION['user_id']) && !isset($_SESSION['user_type'])) {
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

// Agregar un horario
function addHorario($pensum_id, $profesor_id, $aula_id, $hour, $seccion_id, $tipo_horario, $semana_inicio, $semana_fin, $horas_consecutivas, $excepcion, $horaFirstTDid, $diaText)
{
    global $conn;
    $sede_id = $_SESSION['sede_id'];
    try {

        for ($i = 0; $i < $horas_consecutivas; $i++) {
            $query = "INSERT INTO horario (pensum_id, profesor_id, aula_id, user_id, hour, seccion_id, tipo_horario, sede_id, semana_inicio, semana_fin) VALUES (:pensum_id, :profesor_id, :aula_id, :user_id, :hour, :seccion_id, :tipo_horario, :sede_id, :semana_inicio, :semana_fin)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':pensum_id', $pensum_id);
            $stmt->bindParam(':profesor_id', $profesor_id);
            $stmt->bindParam(':aula_id', $aula_id);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':hour', $hour);
            $stmt->bindParam(':seccion_id', $seccion_id);
            $stmt->bindParam(':tipo_horario', $tipo_horario);
            $stmt->bindParam(':sede_id', $sede_id);
            $stmt->bindParam(':semana_inicio', $semana_inicio);
            $stmt->bindParam(':semana_fin', $semana_fin);

            $stmt->execute();

            if ($excepcion == 'true') {
                $query2 = "INSERT INTO excepciones (
                            user_id, 
                            horario_id, 
                            profesor_id, 
                            pensum_id, 
                            hora_dia_id, 
                            dia_text
                        ) VALUES (
                            :user_id, 
                            :horario_id, 
                            :profesor_id, 
                            :pensum_id, 
                            :hora_dia_id, 
                            '$diaText'
                        )";
                $stmt2 = $conn->prepare($query2);
                $stmt2->bindParam(':pensum_id', $pensum_id);
                $stmt2->bindParam(':profesor_id', $profesor_id);
                $stmt2->bindParam(':hora_dia_id', $horaFirstTDid);
                $stmt2->bindParam(':user_id', $_SESSION['user_id']);
                $stmt2->bindParam(':horario_id', $conn->lastInsertId());
                $stmt2->execute();

                $horaFirstTDid++;
            }
            $hour += 7;
        }
        return true;
    } catch (PDOException $e) {
        echo "Error al agregar el horario: " . $e->getMessage();
        return false;
    }
}

// Actualizar un horario
function updateHorario($horario_id, $profesor_id, $aula_id, $pensum_id, $hour, $semana_inicio, $semana_fin, $excepcion, $horaFirstTDid, $diaText)
{
    global $conn;

    try {
        $query = "UPDATE horario 
                  SET pensum_id = :pensum_id, profesor_id = :profesor_id, aula_id = :aula_id, 
                      user_id = :user_id, hour = :hour, update_at = NOW(), semana_inicio = :semana_inicio, semana_fin = :semana_fin
                  WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':pensum_id', $pensum_id);
        $stmt->bindParam(':profesor_id', $profesor_id);
        $stmt->bindParam(':aula_id', $aula_id);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':hour', $hour);
        $stmt->bindParam(':id', $horario_id);
        $stmt->bindParam(':semana_inicio', $semana_inicio);
        $stmt->bindParam(':semana_fin', $semana_fin);

        $stmt->execute();

        if ($excepcion == 'true') {
            $query2 = "INSERT INTO excepciones (
                        user_id, 
                        horario_id, 
                        profesor_id, 
                        pensum_id, 
                        hora_dia_id, 
                        dia_text
                    ) VALUES (
                        :user_id, 
                        :horario_id, 
                        :profesor_id, 
                        :pensum_id, 
                        :hora_dia_id, 
                        '$diaText'
                    )";
            $stmt2 = $conn->prepare($query2);
            $stmt2->bindParam(':pensum_id', $pensum_id);
            $stmt2->bindParam(':profesor_id', $profesor_id);
            $stmt2->bindParam(':hora_dia_id', $horaFirstTDid);
            $stmt2->bindParam(':user_id', $_SESSION['user_id']);
            $stmt2->bindParam(':horario_id', $horario_id);
            $stmt2->execute();
        }

        $response = array(
            'success' => true
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
            'message' => "Error al actualizar el horario: " . $e->getMessage()
        );
        // Convertir el array en formato JSON
        $jsonResponse = json_encode($response);
        http_response_code(404);
        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
        return false;
    }
}

// Eliminar un horario por su ID
function deleteHorario($id)
{
    global $conn;

    try {
        $query = "DELETE FROM horario WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return true;
    } catch (PDOException $e) {
        echo "Error al eliminar el horario: " . $e->getMessage();
        return false;
    }
}

function cargarHorario($seccion_id, $tipo_horario, $semana)
{
    global $conn;
    $sede_id = $_SESSION['sede_id'];
    try {
        $query1 = "SELECT horario.id AS horario_id, horario.pensum_id, horario.profesor_id, horario.aula_id, 
        horario.hour, horario.seccion_id, horario.tipo_horario, horario.semana_inicio, horario.semana_fin, pensum.materia AS pensum_name, profesor.name AS nombre_profesor, profesor.last_name AS apellido_profesor, aula.name AS aula_name, aula.capacity AS aula_capacity
        FROM horario
        INNER JOIN pensum ON horario.pensum_id = pensum.id
        INNER JOIN profesor ON horario.profesor_id = profesor.id
        INNER JOIN aula ON horario.aula_id = aula.id
        WHERE seccion_id = :seccion_id 
        AND tipo_horario = :tipo_horario
        AND :semana BETWEEN semana_inicio AND semana_fin";
        $stmt1 = $conn->prepare($query1);
        $stmt1->bindParam(':seccion_id', $seccion_id);
        $stmt1->bindParam(':semana', $semana);
        $stmt1->bindParam(':tipo_horario', $tipo_horario);

        $stmt1->execute();

        $query2 = "SELECT COUNT(profesor.id) AS activos FROM profesor
        INNER JOIN profesor_disponibilidad_status ON profesor_disponibilidad_status.profesor_id = profesor.id
        WHERE profesor_disponibilidad_status.status = true 
        AND profesor.sede_id = :sede_id";

        $stmt2 = $conn->prepare($query2);
        $stmt2->bindParam(':sede_id', $sede_id);
        $stmt2->execute();

        $query3 = "SELECT COUNT(id) AS sin_crear FROM profesor
        WHERE sede_id = :sede_id AND id NOT IN (SELECT profesor_id FROM profesor_disponibilidad_status)";
        $stmt3 = $conn->prepare($query3);
        $stmt3->bindParam(':sede_id', $sede_id);

        $stmt3->execute();

        $query4 = "SELECT COUNT(id) AS total FROM profesor WHERE sede_id = :sede_id";
        $stmt4 = $conn->prepare($query4);
        $stmt4->bindParam(':sede_id', $sede_id);

        $stmt4->execute();

        $query5 = "SELECT COUNT(profesor.id) AS sin_cargar FROM profesor
        INNER JOIN profesor_disponibilidad_status ON profesor_disponibilidad_status.profesor_id = profesor.id
        WHERE profesor_disponibilidad_status.status = false
        AND profesor.sede_id = :sede_id";
        $stmt5 = $conn->prepare($query5);
        $stmt5->bindParam(':sede_id', $sede_id);

        $stmt5->execute();

        $query6 = "SELECT
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

        $stmt6 = $conn->prepare($query6);
        $stmt6->bindParam(':seccion_id', $seccion_id);
        $stmt6->bindParam(':tipo_horario', $tipo_horario);

        $stmt6->execute();

        $query7 = "SELECT
                        pensum.codigo,
                        pensum.materia,
                        pensum.uc,
                        pensum.horas
                    FROM
                        pensum
                    WHERE
                        pensum.trayecto IN (SELECT trayecto FROM seccion WHERE id = :seccion_id)
                        AND pensum.pnf_id IN (SELECT pnf_id FROM seccion WHERE id = :seccion_id)";

        $stmt7 = $conn->prepare($query7);
        $stmt7->bindParam(':seccion_id', $seccion_id);

        $stmt7->execute();

        $query8 = "SELECT
                        pnf.name AS pnf_name,
                        seccion.trayecto,
                        seccion.seccion
                    FROM
                        seccion
                    INNER JOIN
                        pnf ON seccion.pnf_id = pnf.id
                    WHERE
                        seccion.id = :seccion_id";

        $stmt8 = $conn->prepare($query8);
        $stmt8->bindParam(':seccion_id', $seccion_id);

        $stmt8->execute();

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

        $activos =  $stmt2->fetchAll(PDO::FETCH_ASSOC);
        $sin_crear = $stmt3->fetchAll(PDO::FETCH_ASSOC);
        $total = $stmt4->fetchAll(PDO::FETCH_ASSOC);
        $sin_cargar = $stmt5->fetchAll(PDO::FETCH_ASSOC);

        $activos =  $activos[0];
        $sin_crear = $sin_crear[0];
        $total = $total[0];
        $sin_cargar = $sin_cargar[0];

        $response = array(
            'success' => true,
            'datos' => $stmt1->fetchAll(PDO::FETCH_ASSOC),
            'materias' => $stmt7->fetchAll(PDO::FETCH_ASSOC),
            'profesores' => $stmt6->fetchAll(PDO::FETCH_ASSOC),
            'activos' => $activos['activos'],
            'sin_crear' => $sin_crear['sin_crear'],
            'sin_cargar' => $sin_cargar['sin_cargar'],
            'total' => $total['total'],
            'titleH3' => $stmt8->fetchAll(PDO::FETCH_ASSOC),
            'horas_dia' => $stmt9->fetchAll(PDO::FETCH_ASSOC)
        );
        // Convertir el array en formato JSON
        $jsonResponse = json_encode($response);
        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
    } catch (PDOException $e) {
        $response = array(
            'success' => false,
            'message' => "Error al obtener el horario: " . $e->getMessage()
        );
        // Convertir el array en formato JSON
        $jsonResponse = json_encode($response);
        http_response_code(404);
        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

function cargarSelectSeccion()
{
    global $conn;

    try {
        $query = "SELECT seccion.id, pnf.name AS pnf, seccion.trayecto, seccion.seccion FROM seccion
        INNER JOIN pnf ON pnf.id = seccion.pnf_id
        WHERE seccion.sede_id = :sede AND seccion.pnf_id = :pnf";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':sede', $_SESSION['sede_id']);
        $stmt->bindParam(':pnf', $_SESSION['pnf_id']);

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

function cargarProfesorPerfilProfesional($hour, $tipo_horario, $update, $profesor_id, $semana, $pensum_id, $hor_id)
{
    global $conn;
    $sede_id = $_SESSION['sede_id'];

    $queryDisponibilidadProfe = "SELECT profesor.id
        FROM profesor
        INNER JOIN profesor_disponibilidad_status ON profesor_disponibilidad_status.profesor_id = profesor.id
        INNER JOIN profesor_disponibilidad ON profesor_disponibilidad.profesor_id = profesor.id
        WHERE profesor_disponibilidad.hour = :hour AND 
        profesor_disponibilidad.tipo_horario = :tipo_horario AND
        profesor_disponibilidad.disponibilidad = true AND
        profesor_disponibilidad_status.status = true AND
        profesor.sede_id = :sede_id AND
        profesor.id IN (SELECT profesor_id FROM perfil_profesional WHERE profesion_id IN (SELECT profesion_id FROM pensum_profesion WHERE pensum_id = :pensum_id))";

    // Realizar la consulta y obtener los resultados

    $stmt1 = $conn->prepare($queryDisponibilidadProfe);
    $stmt1->bindParam(':hour', $hour);
    $stmt1->bindParam(':tipo_horario', $tipo_horario);
    $stmt1->bindParam(':sede_id', $sede_id);
    $stmt1->bindParam(':pensum_id', $pensum_id);


    $stmt1->execute();


    $rowCount = $stmt1->rowCount();

    if ($rowCount == 0) {
        if ($update == 'true') {
            $queryProfes = "SELECT profesor.id, profesor.name, profesor.last_name, profesor.cedula 
                            FROM profesor
                            INNER JOIN
                                horario ON horario.profesor_id = profesor.id 
                            WHERE profesor.id = $profesor_id 
                            AND horario.id = $hor_id
                            AND horario.pensum = $pensum_id";
            $stmt5 = $conn->prepare($queryProfes);
            // Realizar la consulta y obtener los resultados
            $stmt5->execute();

            $profesDisponibles = $stmt5->fetchAll(PDO::FETCH_ASSOC);

            $response = [
                'success' => true,
                'profesores' => $profesDisponibles
            ];
            // Convertir el array en formato JSON
            $jsonResponse = json_encode($response);
            // Imprimir el JSON como respuesta
            header('Content-Type: application/json');
            echo $jsonResponse;
            return true;
        }
        $response = [
            'success' => false
        ];
        // Convertir el array en formato JSON
        $jsonResponse = json_encode($response);
        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
        return false;
    }

    $profesoresDisponibles = $stmt1->fetchAll(PDO::FETCH_COLUMN);


    // Consulta 2: Obtener información de los profesores asignados a la hora consultada
    $queryAsignados  = "SELECT profesor_id FROM horario WHERE
    hour = :hour AND 
    sede_id = $sede_id AND
    :semana BETWEEN semana_inicio AND semana_fin";

    // Realizar la consulta y obtener los resultados
    $stmt2 = $conn->prepare($queryAsignados);
    $stmt2->bindParam(':hour', $hour);
    $stmt2->bindParam(':semana', $semana);


    $stmt2->execute();

    $rowCount = $stmt2->rowCount();

    if ($rowCount == 0) {

        $profesoresIds = implode(',', $profesoresDisponibles);

        $queryProfes = "SELECT id, name, last_name, cedula FROM profesor
        WHERE sede_id = $sede_id AND
        id IN ($profesoresIds)";
        $stmt4 = $conn->prepare($queryProfes);
        // Realizar la consulta y obtener los resultados
        $stmt4->execute();

        $profesDisponibles = $stmt4->fetchAll(PDO::FETCH_ASSOC);

        $response = [
            'success' => true,
            'profesores' => $profesDisponibles
        ];
        // Convertir el array en formato JSON
        $jsonResponse = json_encode($response);
        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
        return true;
    }

    $profesoresAsignados = $stmt2->fetchAll(PDO::FETCH_COLUMN);

    // Comparar los resultados de las consultas y obtener los profesores disponibles y asignados
    $profesoresAsignados = array_intersect($profesoresDisponibles, $profesoresAsignados);
    $profesoresNoAsignados = array_diff($profesoresDisponibles, $profesoresAsignados);

    // Crear una nueva consulta SQL para obtener la información de los profesores comunes
    $profesoresAsignadosIds = implode(',', $profesoresAsignados);
    $profesoresNoAsignadosIds = implode(',', $profesoresNoAsignados);

    if ($update == 'true') {
        if (empty($profesoresNoAsignados)) {
            $profesoresNoAsignadosIds = 0;
        }
        $queryProfesoresAsignados = "SELECT id, name, last_name, cedula FROM profesor
        WHERE sede_id = $sede_id AND
        id IN ($profesoresNoAsignadosIds) AND
        id NOT IN ($profesoresAsignadosIds)";

        $queryProfesorUpdate = "SELECT id, name, last_name, cedula FROM profesor 
        WHERE sede_id = $sede_id AND
        id = $profesor_id";

        $stmt7 = $conn->prepare($queryProfesorUpdate);
        $stmt7->execute();

        $profesorUpdateInfo = $stmt7->fetchAll(PDO::FETCH_ASSOC);
    } else {
        if (empty($profesoresNoAsignados)) {
            $profesoresNoAsignadosIds = 0;
        }
        $queryProfesoresAsignados = "SELECT id, name, last_name, cedula FROM profesor
        WHERE sede_id = $sede_id AND
        id IN ($profesoresNoAsignadosIds) AND id NOT IN ($profesoresAsignadosIds)";
    }

    // // Realizar la consulta y obtener los resultados
    $stmt3 = $conn->prepare($queryProfesoresAsignados);
    $stmt3->execute();

    $profesoresAsignadosInfo = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    if ($update == 'true') {
        array_push($profesoresAsignadosInfo, $profesorUpdateInfo[0]);
        $response = [
            'success' => true,
            'profesores' => $profesoresAsignadosInfo
        ];
        // Convertir el array en formato JSON
        $jsonResponse = json_encode($response);
        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
        return true;
    }

    $response = [
        'success' => true,
        'profesores' => $profesoresAsignadosInfo
    ];
    // Convertir el array en formato JSON
    $jsonResponse = json_encode($response);
    // Imprimir el JSON como respuesta
    header('Content-Type: application/json');
    echo $jsonResponse;
    return true;
}

function showDataFormPensumAulas($seccion_id, $update, $horario_id, $hour, $tipo_horario, $semana)
{
    global $conn;

    try {
        $sede_id = $_SESSION['sede_id'];
        // Primero hay que conocer si en la BD existe registros en esta hora
        // En esta consulta busco si hay aulas usadas en esta esta hora

        $queryAulasDisponibles = "SELECT id, name, capacity FROM aula WHERE
                                    sede_id = :sede_id AND
                                    id NOT IN (SELECT aula_id 
                                        FROM horario WHERE hour = :hour 
                                        AND tipo_horario = :tipo_horario AND
                                        :semana BETWEEN semana_inicio AND semana_fin)";
        if ($update == 'true') {
            $queryAulaSelectUpdate = "SELECT id, name, capacity FROM aula WHERE id IN (SELECT aula_id FROM horario WHERE id = :horario_id)";
            $stmt8 = $conn->prepare($queryAulaSelectUpdate);
            $stmt8->bindParam(':horario_id', $horario_id);
            $stmt8->execute();
            $AulaSelectUpdate = $stmt8->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmt2 = $conn->prepare($queryAulasDisponibles);
        $stmt2->bindParam(':sede_id', $sede_id);
        $stmt2->bindParam(':hour', $hour);
        $stmt2->bindParam(':tipo_horario', $tipo_horario);
        $stmt2->bindParam(':semana', $semana);

        $stmt2->execute();

        $rowCount2 = $stmt2->rowCount();

        if ($rowCount2 == 0 && $update != 'true') {
            $response = array(
                'success' => false,
                'status' => 3,
                'message' => 'No hay Aulas disponibles'
            );
            // Convertir el array en formato JSON
            $jsonResponse = json_encode($response);
            // Imprimir el JSON como respuesta
            header('Content-Type: application/json');
            echo $jsonResponse;
            return true;
        }

        if ($update == 'true') {
            $aulasDisponibles = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            array_push($aulasDisponibles, $AulaSelectUpdate[0]);
        } else {
            $aulasDisponibles = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        }


        $queryPensum = "SELECT id, codigo, materia, uc, horas FROM pensum
                        WHERE 
                        pnf_id IN (SELECT pnf_id FROM seccion WHERE id = :seccion_id)
                        AND
                        trayecto IN (SELECT trayecto FROM seccion WHERE id = :seccion_id)";

        $stmt3 = $conn->prepare($queryPensum);
        $stmt3->bindParam(':seccion_id', $seccion_id);
        $stmt3->execute();

        $semanas = [''];

        if ($update == 'true') {
            $querySemanas = "SELECT semana_inicio, semana_fin FROM horario
                        WHERE id = :horario_id";
            $stmt10 = $conn->prepare($querySemanas);
            $stmt10->bindParam(':horario_id', $horario_id);
            $stmt10->execute();
            $semanas = $stmt10->fetchAll(PDO::FETCH_ASSOC);
        }

        $response = [
            'success' => true,
            'aulas' => $aulasDisponibles,
            'pensum' => $stmt3->fetchAll(PDO::FETCH_ASSOC),
            'semanas' => $semanas[0]
        ];
        // Convertir el array en formato JSON
        $jsonResponse = json_encode($response);
        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
    } catch (PDOException $e) {
        $response = array(
            'success' => false,
            'message' => "Error al cargar los datos: " . $e->getMessage()
        );
        // Convertir el array en formato JSON
        $jsonResponse = json_encode($response);
        http_response_code(404);
        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

function dragHorario($data_origen, $data_destino, $tipo_horario)
{
    global $conn;
    try {
        // Primero hay que verificar los datos de el origen y destino, ya que puede ser que el destino
        // este libre, se procederia a crear un registro y no seria un update
        // de lo contrario, serian dos updates, para origen y destino

        if ($data_origen['update'] == 'true') {
            // Hay que comprobar que en el destino este disponible el profesor y el aula del origen
            $queryDisponibilidadProfe = "SELECT profesor_id FROM profesor_disponibilidad WHERE hour = :hour AND tipo_horario = :tipo_horario AND profesor_id = :profesor_id AND disponibilidad = true";

            // Realizar la consulta y obtener los resultados

            $stmt1 = $conn->prepare($queryDisponibilidadProfe);
            $stmt1->bindParam(':hour', $data_destino['posicion']);
            $stmt1->bindParam(':profesor_id', $data_origen['profesor']);
            $stmt1->bindParam(':tipo_horario', $tipo_horario);

            $stmt1->execute();

            $rowCount = $stmt1->rowCount();

            if ($rowCount == 0) {
                $response = array(
                    'success' => false,
                    'status' => 2
                );
                // Convertir el array en formato JSON
                $jsonResponse = json_encode($response);
                // Imprimir el JSON como respuesta
                header('Content-Type: application/json');
                echo $jsonResponse;
                return true;
            }

            $queryDisponibilidadProfe = "SELECT horario.profesor_id, pnf.name, seccion.trayecto, seccion.seccion FROM horario 
            INNER JOIN seccion ON horario.seccion_id = seccion.id
            INNER JOIN pnf ON seccion.pnf_id = pnf.id
            WHERE horario.hour = :hour AND
            horario.tipo_horario = :tipo_horario AND
            horario.profesor_id = :profesor_id";

            // Realizar la consulta y obtener los resultados

            $stmt2 = $conn->prepare($queryDisponibilidadProfe);
            $stmt2->bindParam(':hour', $data_destino['posicion']);
            $stmt2->bindParam(':profesor_id', $data_origen['profesor']);
            $stmt2->bindParam(':tipo_horario', $tipo_horario);

            $stmt2->execute();

            $rowCount = $stmt2->rowCount();

            if ($rowCount == 1) {
                $response = array(
                    'success' => false,
                    'status' => 3,
                    'datos' => $stmt2->fetchAll(PDO::FETCH_ASSOC)
                );
                // Convertir el array en formato JSON
                $jsonResponse = json_encode($response);
                // Imprimir el JSON como respuesta
                header('Content-Type: application/json');
                echo $jsonResponse;
                return true;
            }

            // Ahora consultamos si el aula esta disponible en la nueva hora
            $queryDisponibilidadAula = "SELECT pnf.name, seccion.trayecto, seccion.seccion FROM horario
            INNER JOIN seccion ON horario.seccion_id = seccion.id
            INNER JOIN pnf ON seccion.pnf_id = pnf.id
            WHERE horario.hour = :hour AND horario.tipo_horario = :tipo_horario AND horario.aula_id = :aula_id";

            $stmt3 = $conn->prepare($queryDisponibilidadAula);
            $stmt3->bindParam(':hour', $data_destino['posicion']);
            $stmt3->bindParam(':aula_id', $data_origen['aula']);
            $stmt3->bindParam(':tipo_horario', $tipo_horario);

            $stmt3->execute();

            $rowCount = $stmt3->rowCount();

            if ($rowCount == 1) {
                $response = array(
                    'success' => false,
                    'status' => 4,
                    'datos' => $stmt3->fetchAll(PDO::FETCH_ASSOC)
                );
                // Convertir el array en formato JSON
                $jsonResponse = json_encode($response);
                // Imprimir el JSON como respuesta
                header('Content-Type: application/json');
                echo $jsonResponse;
                return true;
            }
        }
        if ($data_destino['update'] == 'true') {
            // Hay que comprobar que en el destino este disponible el profesor y el aula del origen
            $queryDisponibilidadProfe = "SELECT profesor_id FROM profesor_disponibilidad WHERE hour = :hour AND tipo_horario = :tipo_horario AND profesor_id = :profesor_id AND disponibilidad = true";

            // Realizar la consulta y obtener los resultados

            $stmt1 = $conn->prepare($queryDisponibilidadProfe);
            $stmt1->bindParam(':hour', $data_origen['posicion']);
            $stmt1->bindParam(':profesor_id', $data_destino['profesor']);
            $stmt1->bindParam(':tipo_horario', $tipo_horario);

            $stmt1->execute();

            $rowCount = $stmt1->rowCount();

            if ($rowCount == 0) {
                $response = array(
                    'success' => false,
                    'status' => 5
                );
                // Convertir el array en formato JSON
                $jsonResponse = json_encode($response);
                // Imprimir el JSON como respuesta
                header('Content-Type: application/json');
                echo $jsonResponse;
                return true;
            }

            $queryDisponibilidadProfe = "SELECT horario.profesor_id, pnf.name, seccion.trayecto, seccion.seccion FROM horario
            INNER JOIN seccion ON horario.seccion_id = seccion.id
            INNER JOIN pnf ON seccion.pnf_id = pnf.id
            WHERE horario.hour = :hour AND horario.tipo_horario = :tipo_horario AND horario.profesor_id = :profesor_id";

            // Realizar la consulta y obtener los resultados

            $stmt2 = $conn->prepare($queryDisponibilidadProfe);
            $stmt2->bindParam(':hour', $data_origen['posicion']);
            $stmt2->bindParam(':profesor_id', $data_destino['profesor']);
            $stmt2->bindParam(':tipo_horario', $tipo_horario);

            $stmt2->execute();

            $rowCount = $stmt2->rowCount();

            if ($rowCount == 1) {
                $response = array(
                    'success' => false,
                    'status' => 6,
                    'datos' => $stmt2->fetchAll(PDO::FETCH_ASSOC)
                );
                // Convertir el array en formato JSON
                $jsonResponse = json_encode($response);
                // Imprimir el JSON como respuesta
                header('Content-Type: application/json');
                echo $jsonResponse;
                return true;
            }

            // Ahora consultamos si el aula esta disponible en la nueva hora
            $queryDisponibilidadAula = "SELECT pnf.name, seccion.trayecto, seccion.seccion FROM horario
            INNER JOIN seccion ON horario.seccion_id = seccion.id
            INNER JOIN pnf ON seccion.pnf_id = pnf.id
            WHERE horario.hour = :hour AND horario.tipo_horario = :tipo_horario AND horario.aula_id = :aula_id";

            $stmt3 = $conn->prepare($queryDisponibilidadAula);
            $stmt3->bindParam(':hour', $data_origen['posicion']);
            $stmt3->bindParam(':aula_id', $data_destino['aula']);
            $stmt3->bindParam(':tipo_horario', $tipo_horario);

            $stmt3->execute();

            $rowCount = $stmt3->rowCount();

            if ($rowCount == 1) {
                $response = array(
                    'success' => false,
                    'status' => 7,
                    'datos' => $stmt3->fetchAll(PDO::FETCH_ASSOC)
                );
                // Convertir el array en formato JSON
                $jsonResponse = json_encode($response);
                // Imprimir el JSON como respuesta
                header('Content-Type: application/json');
                echo $jsonResponse;
                return true;
            }
        }
        if ($data_origen['update'] == 'false' && $data_destino['update'] == 'true') {
            $query = "UPDATE horario 
                  SET hour = :hour, update_at = NOW() 
                  WHERE id = :id";
            $stmt = $conn->prepare($query);

            $stmt->bindParam(':hour', $data_origen['posicion']);
            $stmt->bindParam(':id', $data_destino['horario_id']);

            $stmt->execute();

            $response = array(
                'success' => true
            );
            // Convertir el array en formato JSON
            $jsonResponse = json_encode($response);
            // Imprimir el JSON como respuesta
            header('Content-Type: application/json');
            echo $jsonResponse;
            return true;
        }
        if ($data_origen['update'] == 'true' && $data_destino['update'] == 'false') {
            $query = "UPDATE horario 
            SET hour = :hour, update_at = NOW() 
            WHERE id = :id";
            $stmt = $conn->prepare($query);

            $stmt->bindParam(':hour', $data_destino['posicion']);
            $stmt->bindParam(':id', $data_origen['horario_id']);

            $stmt->execute();

            $response = array(
                'success' => true
            );
            // Convertir el array en formato JSON
            $jsonResponse = json_encode($response);
            // Imprimir el JSON como respuesta
            header('Content-Type: application/json');
            echo $jsonResponse;
            return true;
        }
        if ($data_origen['update'] == 'true' && $data_destino['update'] == 'true') {
            $queryOrigen = "UPDATE horario 
            SET pensum_id = :pensum_id, profesor_id = :profesor_id, aula_id = :aula_id, 
                user_id = :user_id, hour = :hour, update_at = NOW()
            WHERE id = :id";
            $stmt1 = $conn->prepare($queryOrigen);
            $stmt1->bindParam(':pensum_id', $data_destino['pensum']);
            $stmt1->bindParam(':profesor_id', $data_destino['profesor']);
            $stmt1->bindParam(':aula_id', $data_destino['aula']);
            $stmt1->bindParam(':user_id', $_SESSION['user_id']);
            $stmt1->bindParam(':hour', $data_destino['posicion']);
            $stmt1->bindParam(':id', $data_origen['horario_id']);

            $stmt1->execute();

            $queryDestino = "UPDATE horario 
            SET pensum_id = :pensum_id, profesor_id = :profesor_id, aula_id = :aula_id, 
                user_id = :user_id, hour = :hour, update_at = NOW()
            WHERE id = :id";
            $stmt2 = $conn->prepare($queryDestino);
            $stmt2->bindParam(':pensum_id', $data_origen['pensum']);
            $stmt2->bindParam(':profesor_id', $data_origen['profesor']);
            $stmt2->bindParam(':aula_id', $data_origen['aula']);
            $stmt2->bindParam(':user_id', $_SESSION['user_id']);
            $stmt2->bindParam(':hour', $data_origen['posicion']);
            $stmt2->bindParam(':id', $data_destino['horario_id']);

            $stmt2->execute();

            $response = array(
                'success' => true
            );
            // Convertir el array en formato JSON
            $jsonResponse = json_encode($response);
            // Imprimir el JSON como respuesta
            header('Content-Type: application/json');
            echo $jsonResponse;
            return true;
        }
    } catch (PDOException $e) {
        $response = array(
            'success' => false,
            'error' => "Error: " . $e->getMessage()
        );
        // Convertir el array en formato JSON
        $jsonResponse = json_encode($response);
        // Imprimir el JSON como respuesta
        http_response_code(400);
        header('Content-Type: application/json');
        echo $jsonResponse;
        return false;
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

function verificarDatosHorasConsecutivas($profesor_id, $aula_id, $hour, $tipo_horario, $semana_inicio, $semana_fin, $horas_consecutivas, $pensum_id)
{

    function verificarDisponibilidadProfesor($profesor_id, $hour, $tipo_horario, $semana_inicio, $semana_fin, $seccion_id)
    {
        global $conn;

        try {
            $query1 = "SELECT profesor_id FROM profesor_disponibilidad WHERE profesor_id = :profesor_id AND hour = :hour AND tipo_horario = :tipo_horario AND disponibilidad = true";
            $stmt1 = $conn->prepare($query1);
            $stmt1->bindParam(':profesor_id', $profesor_id);
            $stmt1->bindParam(':tipo_horario', $tipo_horario);
            $stmt1->bindParam(':hour', $hour);

            $stmt1->execute();

            $rowCount1 = $stmt1->rowCount();

            if ($rowCount1 == 0) {
                return 2;
            }

            $query2 = "SELECT profesor_id FROM horario
                        WHERE seccion_id = :seccion_id AND 
                        profesor_id = :profesor_id AND
                        tipo_horario = :tipo_horario AND
                        hour = :hour AND
                        :semana_inicio BETWEEN semana_inicio AND semana_fin AND
                        :semana_fin BETWEEN semana_inicio AND semana_fin";
            $stmt2 = $conn->prepare($query2);
            $stmt2->bindParam(':seccion_id', $seccion_id);
            $stmt2->bindParam(':profesor_id', $profesor_id);
            $stmt2->bindParam(':tipo_horario', $tipo_horario);
            $stmt2->bindParam(':hour', $hour);
            $stmt2->bindParam(':semana_inicio', $semana_inicio);
            $stmt2->bindParam(':semana_fin', $semana_fin);


            $stmt2->execute();

            $rowCount2 = $stmt2->rowCount();

            if ($rowCount2 > 0) {
                return 3;
            }
            return 1;
        } catch (PDOException $e) {
            $response = array(
                'success' => false,
                'message' => "Error: " . $e->getMessage()
            );
            // Convertir el array en formato JSON
            $jsonResponse = json_encode($response);
            http_response_code(404);
            // Imprimir el JSON como respuesta
            header('Content-Type: application/json');
            echo $jsonResponse;
        }
    }
    function verificarDisponibilidadAula($aula_id, $hour, $tipo_horario, $semana_inicio, $semana_fin)
    {
        global $conn;
        try {
            $query1 = "SELECT aula_id FROM horario
            WHERE aula_id = :aula_id AND hour = :hour AND tipo_horario = :tipo_horario 
            AND :semana_inicio BETWEEN semana_inicio AND semana_fin
            AND :semana_fin BETWEEN semana_inicio AND semana_fin";
            $stmt1 = $conn->prepare($query1);
            $stmt1->bindParam(':aula_id', $aula_id);
            $stmt1->bindParam(':tipo_horario', $tipo_horario);
            $stmt1->bindParam(':hour', $hour);
            $stmt1->bindParam(':semana_inicio', $semana_inicio);
            $stmt1->bindParam(':semana_fin', $semana_fin);


            $stmt1->execute();

            $rowCount1 = $stmt1->rowCount();

            if ($rowCount1 > 0) {
                return 2;
            }
            return 1;
        } catch (PDOException $e) {
            $response = array(
                'success' => false,
                'message' => "Error: " . $e->getMessage()
            );
            // Convertir el array en formato JSON
            $jsonResponse = json_encode($response);
            http_response_code(404);
            // Imprimir el JSON como respuesta
            header('Content-Type: application/json');
            echo $jsonResponse;
        }
    }
    function verificarHorasSeguidasPensum($pensum_id, $profesor_id, $hour, $tipo_horario, $semana_inicio, $semana_fin)
    {
        global $conn;
        try {
            $query1 = "SELECT pensum_id FROM horario
            WHERE pensum_id = :pensum_id AND
            profesor_id = :profesor_id AND
            hour = :hour AND tipo_horario = :tipo_horario 
            AND :semana_inicio BETWEEN semana_inicio AND semana_fin
            AND :semana_fin BETWEEN semana_inicio AND semana_fin";
            $stmt1 = $conn->prepare($query1);
            $stmt1->bindParam(':pensum_id', $pensum_id);
            $stmt1->bindParam(':profesor_id', $profesor_id);
            $stmt1->bindParam(':tipo_horario', $tipo_horario);
            $stmt1->bindParam(':hour', $hour);
            $stmt1->bindParam(':semana_inicio', $semana_inicio);
            $stmt1->bindParam(':semana_fin', $semana_fin);


            $stmt1->execute();

            $rowCount1 = $stmt1->rowCount();

            if ($rowCount1 > 0) {
                return 2;
            }
            return 1;
        } catch (PDOException $e) {
            $response = array(
                'success' => false,
                'message' => "Error: " . $e->getMessage()
            );
            // Convertir el array en formato JSON
            $jsonResponse = json_encode($response);
            http_response_code(404);
            // Imprimir el JSON como respuesta
            header('Content-Type: application/json');
            echo $jsonResponse;
        }
    }
    $message_profe = 'OK';
    $message_aula = 'OK';
    $message_pensum = 'OK';
    $limited = 0;
    for ($i = 0; $i < $horas_consecutivas; $i++) {

        $profesor = verificarDisponibilidadProfesor($profesor_id, $hour, $tipo_horario, $semana_inicio, $semana_fin, $semana_fin);

        if ($profesor == 2) {
            $message_profe = 'El profesor no se encuentra disponible en esas horas consecutivas, intenta con menos horas';
        }
        if ($profesor == 3) {
            $message_profe = 'El profesor se encuentra asignado ha otro horario a esa hora o periodo de semanas, intenta con menos horas o semanas';
        }

        $aula = verificarDisponibilidadAula($aula_id, $hour, $tipo_horario, $semana_inicio, $semana_fin);

        if ($aula == 2) {
            $message_aula = 'El aula ya esta siendo ocupada por otra seccion a esa hora o periodo de semanas, intenta con menos horas o semanas';
        }

        $pensum = verificarHorasSeguidasPensum($pensum_id, $profesor_id, $hour, $tipo_horario, $semana_inicio, $semana_fin);

        if ($pensum == 2) {
            $limited++;
        }

        $hour += 7;
    }

    if ($limited >= 4) {
        $message_pensum = 'No puedes agregar más horas de este Pensum en el periodo de las semanas seleccionadas';
    } else {
        $pensum = 1;
    }

    $profesor_datos = [
        'status' => $profesor,
        'message' => $message_profe
    ];

    $aula_datos = [
        'status' => $aula,
        'message' => $message_aula
    ];

    $pensum_datos = [
        'status' => $pensum,
        'message' => $message_pensum
    ];

    $response = [
        'success' => true,
        'profesor' => $profesor_datos,
        'aula' => $aula_datos,
        'pensum' => $pensum_datos
    ];
    // Convertir el array en formato JSON
    $jsonResponse = json_encode($response);
    // Imprimir el JSON como respuesta
    header('Content-Type: application/json');
    echo $jsonResponse;
    return true;
}

function verDisponibilidadProfe($texto)
{
    global $conn;

    try {
        $sede_id = $_SESSION['sede_id'];
        switch ($texto) {
            case 'todos':
                $query = "SELECT profesor.id, profesor.name, profesor.last_name, profesor.cedula, profesor_disponibilidad_status.status FROM profesor
                INNER JOIN profesor_disponibilidad_status ON profesor_disponibilidad_status.profesor_id = profesor.id
                WHERE profesor_disponibilidad_status.status = true
                AND profesor.sede_id = $sede_id
                ORDER BY profesor_disponibilidad_status.status ASC";
                $stmt = $conn->prepare($query);
                $stmt->execute();

                $profesores_status_true = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $profesores = $profesores_status_true;

                $query = "SELECT profesor.id, profesor.name, profesor.last_name, profesor.cedula, profesor_disponibilidad_status.status FROM profesor
                INNER JOIN profesor_disponibilidad_status ON profesor_disponibilidad_status.profesor_id = profesor.id
                WHERE profesor_disponibilidad_status.status = false
                AND profesor.sede_id = $sede_id
                ORDER BY profesor_disponibilidad_status.status ASC";
                $stmt = $conn->prepare($query);
                $stmt->execute();

                $rowCount = $stmt->rowCount();
                if ($rowCount > 0) {
                    $profesores_status_false = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $profesores = array_merge($profesores_status_true, $profesores_status_false);
                }

                $query = "SELECT id, name, last_name, cedula FROM profesor
                        WHERE sede_id = $sede_id
                        AND id NOT IN (SELECT profesor_id FROM profesor_disponibilidad_status ORDER BY status ASC)";

                $stmt = $conn->prepare($query);
                $stmt->execute();

                $rowCount = $stmt->rowCount();
                if ($rowCount > 0) {
                    $profesores_status_sin_crear = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $newStatus = "sinCrear";
                    foreach ($profesores_status_sin_crear as &$person) {
                        $person["status"] = $newStatus;
                    }
                    unset($person); // Liberar la referencia
                    $profesores = array_merge($profesores, $profesores_status_sin_crear);
                }
                break;
            case 'cargados':
                $query = "SELECT profesor.id, profesor.name, profesor.last_name, profesor.cedula, profesor_disponibilidad_status.status FROM profesor
                INNER JOIN profesor_disponibilidad_status ON profesor_disponibilidad_status.profesor_id = profesor.id
                WHERE profesor_disponibilidad_status.status = true
                AND profesor.sede_id = $sede_id
                ORDER BY profesor_disponibilidad_status.status ASC";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'sinCargar':
                $query = "SELECT profesor.id, profesor.name, profesor.last_name, profesor.cedula, profesor_disponibilidad_status.status FROM profesor
                INNER JOIN profesor_disponibilidad_status ON profesor_disponibilidad_status.profesor_id = profesor.id
                WHERE profesor_disponibilidad_status.status = false
                AND profesor.sede_id = $sede_id
                ORDER BY profesor_disponibilidad_status.status ASC";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'sinCrear':
                $query = "SELECT profesor.id, profesor.name, profesor.last_name, profesor.cedula FROM profesor
                WHERE profesor.sede_id = $sede_id
                AND id NOT IN (SELECT profesor_id FROM profesor_disponibilidad_status ORDER BY status ASC)";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $profesores_status_false = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($profesores_status_false as &$item) {
                    $item['status'] = 'sinCrear';
                }
                unset($item); // Liberar la referencia
                $profesores = $profesores_status_false;
                break;
            default:
                return false;
                break;
        }
        $response = array(
            'success' => true,
            'profesores' => $profesores
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
            'message' => "Error: " . $e->getMessage()
        );
        // Convertir el array en formato JSON
        $jsonResponse = json_encode($response);
        http_response_code(404);
        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}
function desbloquearDispoProfe($id)
{
    global $conn;
    try {
        $query = "UPDATE profesor_disponibilidad_status 
                    SET 
                        status = false,
                        updated_at = NOW()
                    WHERE 
                        profesor_id = $id";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        $response = array(
            'success' => false,
            'message' => "Error: " . $e->getMessage()
        );
        // Convertir el array en formato JSON
        $jsonResponse = json_encode($response);
        http_response_code(404);
        // Imprimir el JSON como respuesta
        header('Content-Type: application/json');
        echo $jsonResponse;
    }
}

function traerTodosProfesoresExcepcion($hour, $tipo_horario)
{
    global $conn;
    $sede_id = $_SESSION['sede_id'];
    $query = "SELECT profesor.id, profesor.name, profesor.last_name, profesor.cedula
            FROM profesor
            INNER JOIN profesor_disponibilidad_status ON profesor_disponibilidad_status.profesor_id = profesor.id
            INNER JOIN profesor_disponibilidad ON profesor_disponibilidad.profesor_id = profesor.id
            WHERE profesor_disponibilidad.hour = $hour AND 
            profesor_disponibilidad.tipo_horario = '$tipo_horario' AND
            profesor_disponibilidad.disponibilidad = true AND
            profesor_disponibilidad_status.status = true AND
            profesor.sede_id = $sede_id AND
            profesor.id NOT IN (SELECT profesor_id FROM horario WHERE hour = $hour)";
    $stmt = $conn->prepare($query);
    $stmt->execute();


    $response = [
        'success' => true,
        'profesores' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ];

    $jsonResponse = json_encode($response);
    // Imprimir el JSON como respuesta
    header('Content-Type: application/json');
    echo $jsonResponse;
    return true;
}


switch ($_POST['tipo']) {
    case 'cargarHorario':
        cargarHorario($_POST['seccion_id'], $_POST['tipo_horario'], $_POST['semana']);
        break;
    case 'cargarSelectSeccion':
        cargarSelectSeccion();
        break;
    case 'crear':
        addHorario($_POST['pensum_id'], $_POST['profesor_id'], $_POST['aula_id'], $_POST['hour'], $_POST['seccion_id'], $_POST['tipo_horario'], $_POST['semana_inicio'], $_POST['semana_fin'], $_POST['horas_consecutivas'], $_POST['excepcion'], $_POST['horaFirstTDid'], $_POST['diaText']);
        break;
    case 'drag':
        dragHorario($_POST['data_origen'], $_POST['data_destino'], $_POST['tipo_horario']);
        break;
    case 'actualizar':
        updateHorario($_POST['horario_id'], $_POST['profesor_id'], $_POST['aula_id'], $_POST['pensum_id'], $_POST['hour'], $_POST['semana_inicio'], $_POST['semana_fin'], $_POST['excepcion'], $_POST['horaFirstTDid'], $_POST['diaText']);
        break;
    case 'cargarSemanas':
        cargarSemanas($_POST['seccion_id'], $_POST['tipo_horario']);
        break;
    case 'verificarDatosHorasConsecutivas':
        verificarDatosHorasConsecutivas($_POST['profesor_id'], $_POST['aula_id'], $_POST['hour'], $_POST['tipo_horario'], $_POST['semana_inicio'], $_POST['semana_fin'], $_POST['horas_consecutivas'], $_POST['pensum_id']);
        break;
    case 'borrar':
        deleteHorario($_POST['id']);
        break;
    case 'verDisponibilidadProfe':
        verDisponibilidadProfe($_POST['boton']);
        break;
    case 'desbloquearDispoProfe':
        desbloquearDispoProfe($_POST['id']);
        break;
    case 'showDataFormPensumAulas':
        showDataFormPensumAulas($_POST['seccion_id'], $_POST['update'], $_POST['horario_id'], $_POST['hour'], $_POST['tipo_horario'], $_POST['semana']);
        break;
    case 'cargarProfesorPerfilProfesional':
        cargarProfesorPerfilProfesional($_POST['hour'], $_POST['tipo_horario'], $_POST['update'], $_POST['profesor_id'], $_POST['semana'], $_POST['pensum_id'], $_POST['hor_id']);
        break;
    case 'traerTodosProfesoresExcepcion':
        traerTodosProfesoresExcepcion($_POST['hour'], $_POST['tipo_horario']);
        break;
}
