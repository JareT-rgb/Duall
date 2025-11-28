<?php
require 'db_connection.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Solicitud inválida.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validación de campos, incluyendo los nuevos de dirección
    $required_fields = [
        'nombre', 'ap_paterno', 'ap_materno', 'correo_electronico', 'telefono', 
        'colonia', 'calle', 'numero_casa',
        'n_control', 'carrera', 'semestre', 'grupo', 'turno', 'contrasena'
    ];
    $errors = [];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $errors[] = "El campo '$field' es obligatorio.";
        }
    }

    if (!empty($errors)) {
        $response['message'] = implode(' ', $errors);
        echo json_encode($response);
        exit;
    }

    // Limpieza y asignación de variables
    $nombre = trim($data['nombre']);
    $ap_paterno = trim($data['ap_paterno']);
    $ap_materno = trim($data['ap_materno']);
    $correo = trim($data['correo_electronico']);
    $telefono = trim($data['telefono']);
    
    // Unir los campos de dirección en un solo string
    $colonia = trim($data['colonia']);
    $calle = trim($data['calle']);
    $numero_casa = trim($data['numero_casa']);
    $direccion = "$calle $numero_casa, $colonia";

    $n_control = trim($data['n_control']);
    $carrera = trim($data['carrera']);
    $semestre = (int)$data['semestre'];
    $grupo = trim($data['grupo']);
    $turno = trim($data['turno']);
    $contrasena = $data['contrasena'];

    // Validaciones adicionales
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'El formato del correo electrónico no es válido.';
        echo json_encode($response);
        exit;
    }

    if (strlen($contrasena) < 8) {
        $response['message'] = 'La contraseña debe tener al menos 8 caracteres.';
        echo json_encode($response);
        exit;
    }

    try {
        // Verificar si el correo o el número de control ya existen
        $stmt = $pdo->prepare("SELECT id_alumno FROM alumnos WHERE correo_electronico = ? OR n_control = ?");
        $stmt->execute([$correo, $n_control]);
        if ($stmt->fetch()) {
            $response['message'] = 'El correo electrónico o el número de control ya están registrados.';
            echo json_encode($response);
            exit;
        }

        // Hashear la contraseña
        $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

        // Insertar el nuevo alumno (Query actualizada con el campo dirección)
        $sql = "INSERT INTO alumnos (nombre, ap_paterno, ap_materno, correo_electronico, telefono, direccion, n_control, carrera, semestre, grupo, turno, contrasena) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        $params = [
            $nombre, $ap_paterno, $ap_materno, $correo, $telefono, 
            $direccion, 
            $n_control, $carrera, $semestre, $grupo, $turno, $hashed_password
        ];
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            $response = ['success' => true, 'message' => '¡Registro exitoso! Ahora puedes iniciar sesión.'];
        } else {
            $response['message'] = 'Error al registrar el alumno.';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    }
}

echo json_encode($response);
?>
