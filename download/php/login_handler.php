<?php
session_start();
require 'db_connection.php';
require 'config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Solicitud inválida.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['role'], $data['email'], $data['password'])) {
        $role = $data['role'];
        $email = trim($data['email']);
        $password = $data['password'];

        if ($role === 'alumno') {
            try {
                $stmt = $pdo->prepare("SELECT id_alumno, nombre, contrasena FROM alumnos WHERE correo_electronico = ?");
                $stmt->execute([$email]);
                $alumno = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($alumno && password_verify($password, $alumno['contrasena'])) {
                    $_SESSION['user_id'] = $alumno['id_alumno'];
                    $_SESSION['user_role'] = 'alumno';
                    $_SESSION['user_name'] = $alumno['nombre'];
                    $response = ['success' => true, 'message' => 'Inicio de sesión exitoso.', 'redirect' => 'student_dashboard.php'];
                } else {
                    $response['message'] = 'Correo electrónico o contraseña incorrectos.';
                }
            } catch (PDOException $e) {
                $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
            }
        } elseif ($role === 'admin') {
            try {
                $stmt = $pdo->prepare("SELECT * FROM administradores WHERE usuario = ?");
                $stmt->execute([$email]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                // La contraseña en la BD no está hasheada según la descripción
                if ($admin && $password === $admin['contrasena']) {
                    $_SESSION['user_id'] = $admin['id_admin'];
                    $_SESSION['user_role'] = 'admin';
                    $_SESSION['user_name'] = $admin['usuario'];
                    $response = ['success' => true, 'message' => 'Inicio de sesión de administrador exitoso.', 'redirect' => 'admin_dashboard.php'];
                } else {
                    $response['message'] = 'Usuario o contraseña de administrador incorrectos.';
                }
            } catch (PDOException $e) {
                $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
            }
        } else {
            $response['message'] = 'Rol no válido seleccionado.';
        }
    } else {
        $response['message'] = 'Por favor, completa todos los campos.';
    }
}

echo json_encode($response);
?>
