<?php
session_start();
header('Content-Type: application/json');

require 'db_connection.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit();
}

// Check for POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$id_registro = $data['id_registro'] ?? null;
$id_alumno = $_SESSION['user_id'];

if (!$id_registro) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit();
}

try {
    $pdo->beginTransaction();

    // Verify that the registration belongs to the current student and is active
    $stmt_verify = $pdo->prepare("SELECT * FROM registro_alumnos WHERE id_registro = ? AND id_alumno = ? AND estatus = 'Aceptado'");
    $stmt_verify->execute([$id_registro, $id_alumno]);
    $registro = $stmt_verify->fetch();

    if (!$registro) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'No se encontró un registro válido o activo para solicitar la baja.']);
        exit();
    }

    // Update the status to 'Baja Solicitada'
    $stmt_update = $pdo->prepare("UPDATE registro_alumnos SET estatus = 'Baja Solicitada' WHERE id_registro = ?");
    $stmt_update->execute([$id_registro]);
    
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Tu solicitud de baja ha sido enviada correctamente. El administrador la revisará a la brevedad.']);

} catch (PDOException $e) {
    $pdo->rollBack();
    // For development, you can show the error. In production, log it.
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos. Por favor, contacta al administrador.']);
}
?>
