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
            // Verificar si el alumno ya se postuló a esta empresa
            $check_stmt = $pdo->prepare("SELECT id_registro FROM registro_alumnos WHERE id_alumno = ? AND id_empresa = ?");
            $check_stmt->execute([$id_alumno, $id_empresa]);

            if ($check_stmt->fetch()) {
                $response['message'] = 'Ya te has postulado a esta empresa anteriormente.';
            } else {
                // Insertar la nueva postulación
                $insert_stmt = $pdo->prepare("INSERT INTO registro_alumnos (id_alumno, id_empresa, estatus) VALUES (?, ?, 'Pendiente')");
                if ($insert_stmt->execute([$id_alumno, $id_empresa])) {
                    $response = ['success' => true, 'message' => '¡Te has postulado exitosamente!'];
                } else {
                    $response['message'] = 'No se pudo completar la postulación.';
                }
            }
        } catch (PDOException $e) {
            $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'No se proporcionó el ID de la empresa.';
    }
}

echo json_encode($response);
?>
