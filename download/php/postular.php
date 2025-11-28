<?php
session_start();
require 'db_connection.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Solicitud no válida.'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    $response['message'] = 'No tienes permiso para realizar esta acción.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id_empresa = $data['id_empresa'] ?? null;
    $id_alumno = $_SESSION['user_id'];

    if ($id_empresa) {
        try {
            // 1. Verificar si hay un rechazo reciente
            $check_stmt = $pdo->prepare("
                SELECT estatus, fecha_rechazo 
                FROM registro_alumnos 
                WHERE id_alumno = ? AND id_empresa = ?
                ORDER BY fecha_registro DESC 
                LIMIT 1
            ");
            $check_stmt->execute([$id_alumno, $id_empresa]);
            $last_postulacion = $check_stmt->fetch(PDO::FETCH_ASSOC);

            if ($last_postulacion && $last_postulacion['estatus'] === 'Rechazado') {
                $fecha_rechazo = new DateTime($last_postulacion['fecha_rechazo']);
                $ahora = new DateTime();
                $diferencia = $ahora->getTimestamp() - $fecha_rechazo->getTimestamp();

                if ($diferencia < 12 * 3600) { // 12 horas en segundos
                    $tiempo_restante = 12 * 3600 - $diferencia;
                    $horas_restantes = floor($tiempo_restante / 3600);
                    $minutos_restantes = floor(($tiempo_restante % 3600) / 60);
                    $response['message'] = "Fuiste rechazado recientemente de esta empresa. Debes esperar {$horas_restantes}h {$minutos_restantes}m para volver a postularte.";
                } else {
                    // Si ya pasaron las 12 horas, permite postularse de nuevo
                    insertarPostulacion($pdo, $id_alumno, $id_empresa, $response);
                }
            } elseif ($last_postulacion && $last_postulacion['estatus'] !== 'Rechazado') {
                 $response['message'] = 'Ya tienes una postulación activa o aceptada en esta empresa.';
            } else {
                // Si no hay postulación previa, la inserta
                insertarPostulacion($pdo, $id_alumno, $id_empresa, $response);
            }

        } catch (PDOException $e) {
>>>>>>> a4e8b3b... feat: implement 12h restriction on company rejection
            $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'No se proporcionó el ID de la empresa.';
    }
}

// Función auxiliar para no repetir código
function insertarPostulacion($pdo, $id_alumno, $id_empresa, &$response) {
    $insert_stmt = $pdo->prepare("INSERT INTO registro_alumnos (id_alumno, id_empresa, estatus) VALUES (?, ?, 'Pendiente')");
    if ($insert_stmt->execute([$id_alumno, $id_empresa])) {
        $response = ['success' => true, 'message' => '¡Te has postulado exitosamente!'];
    } else {
        $response['message'] = 'No se pudo completar la postulación.';
    }
}

echo json_encode($response);
?>
