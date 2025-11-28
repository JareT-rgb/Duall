<?php
session_start();
require 'db_connection.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Solicitud inválida.'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode($response);
    exit();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    $response['message'] = 'No tienes permiso para realizar esta acción.';
    echo json_encode($response);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];

// --- Validación de Campos ---
$required_fields = ['nombre', 'ap_paterno', 'ap_materno', 'correo_electronico', 'telefono', 'direccion'];
foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        $response['message'] = "El campo '$field' es obligatorio.";
        echo json_encode($response);
        exit();
    }
}

// Limpieza y asignación
$nombre = trim($data['nombre']);
$ap_paterno = trim($data['ap_paterno']);
$ap_materno = trim($data['ap_materno']);
$correo = trim($data['correo_electronico']);
$telefono = trim($data['telefono']);
$direccion = trim($data['direccion']);
$contrasena = $data['contrasena'] ?? '';

// --- Validaciones Adicionales ---
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'El formato del correo electrónico no es válido.';
    echo json_encode($response);
    exit();
}

// Verificar si el nuevo correo ya existe para otro usuario
try {
    $stmt = $pdo->prepare("SELECT id_alumno FROM alumnos WHERE correo_electronico = ? AND id_alumno != ?");
    $stmt->execute([$correo, $user_id]);
    if ($stmt->fetch()) {
        $response['message'] = 'Este correo electrónico ya está en uso por otra cuenta.';
        echo json_encode($response);
        exit();
    }

    // --- Construcción de la Consulta ---
    $fields_to_update = [
        'nombre' => $nombre,
        'ap_paterno' => $ap_paterno,
        'ap_materno' => $ap_materno,
        'correo_electronico' => $correo,
        'telefono' => $telefono,
        'direccion' => $direccion,
    ];

    $sql = "UPDATE alumnos SET nombre = :nombre, ap_paterno = :ap_paterno, ap_materno = :ap_materno, correo_electronico = :correo_electronico, telefono = :telefono, direccion = :direccion";

    // Si se proporcionó una nueva contraseña, añadirla a la consulta
    if (!empty($contrasena)) {
        if (strlen($contrasena) < 8) {
            $response['message'] = 'La nueva contraseña debe tener al menos 8 caracteres.';
            echo json_encode($response);
            exit();
        }
        $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);
        $sql .= ", contrasena = :contrasena";
        $fields_to_update['contrasena'] = $hashed_password;
    }

    $sql .= " WHERE id_alumno = :id_alumno";
    $fields_to_update['id_alumno'] = $user_id;

    // --- Ejecución de la Consulta ---
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($fields_to_update)) {
        $response = ['success' => true, 'message' => 'Perfil actualizado correctamente.'];
        // Actualizar el nombre en la sesión si cambió
        $_SESSION['user_name'] = $nombre; 
    } else {
        $response['message'] = 'No se pudo actualizar el perfil.';
    }

} catch (PDOException $e) {
    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
}

echo json_encode($response);
?>
