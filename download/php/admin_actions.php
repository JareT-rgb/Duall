<?php
session_start();
require 'db_connection.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Acción no válida.'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $response['message'] = 'No tienes permiso para realizar esta acción.';
    echo json_encode($response);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? null;

    switch ($action) {
        case 'update_status':
            $id_registro = $data['id_registro'] ?? 0;
            $estatus = $data['estatus'] ?? '';
            
            if ($id_registro > 0 && in_array($estatus, ['Pendiente', 'Aceptado', 'Rechazado'])) {
                try {
                    $stmt = $pdo->prepare("UPDATE registro_alumnos SET estatus = ? WHERE id_registro = ?");
                    if ($stmt->execute([$estatus, $id_registro])) {
                        $response = ['success' => true, 'message' => 'Estatus actualizado.'];
                    } else {
                        $response['message'] = 'No se pudo actualizar el estatus.';
                    }
                } catch (PDOException $e) {
                    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
                }
            } else {
                $response['message'] = 'Datos inválidos para actualizar estatus.';
            }
            break;

        case 'update_alumno_report':
            $id_registro = $data['id_registro'] ?? 0;
            $field = $data['field'] ?? '';
            $value = $data['value'] ?? '';

            if ($id_registro > 0 && in_array($field, ['puesto', 'fecha_ingreso', 'fecha_egreso'])) {
                try {
                    // Si el valor de fecha está vacío, guardarlo como NULL
                    $db_value = ($value === '') ? null : $value;

                    $stmt = $pdo->prepare("UPDATE registro_alumnos SET {$field} = ? WHERE id_registro = ?");
                    if ($stmt->execute([$db_value, $id_registro])) {
                        $response = ['success' => true, 'message' => 'Campo actualizado.'];
                    } else {
                        $response['message'] = 'No se pudo actualizar el campo.';
                    }
                } catch (PDOException $e) {
                    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
                }
            } else {
                $response['message'] = 'Datos inválidos para actualizar el reporte.';
            }
            break;

        case 'add_empresa':
            // Recoger todos los campos del formulario
            $nombre_empresa = $data['nombre_empresa'] ?? '';
            $razon_social = $data['razon_social'] ?? null;
            $rfc = $data['rfc'] ?? null;
            $giro = $data['giro'] ?? null;
            $descripcion = $data['descripcion'] ?? '';
            $carrera_afin = $data['carrera_afin'] ?? '';
            $perfil_alumno = $data['perfil_alumno'] ?? null;
            $direccion = $data['direccion'] ?? '';
            $telefono_empresa = $data['telefono_empresa'] ?? '';
            $correo_empresa = $data['correo_empresa'] ?? '';
            $nombre_contacto = $data['nombre_contacto'] ?? null;
            $telefono_contacto = $data['telefono_contacto'] ?? null;
            
            if (!empty($nombre_empresa) && !empty($descripcion) && !empty($carrera_afin) && !empty($direccion) && !empty($telefono_empresa) && !empty($correo_empresa)) {
                try {
                    $stmt = $pdo->prepare(
                        "INSERT INTO empresas (nombre_empresa, razon_social, rfc, giro, descripcion, carrera_afin, perfil_alumno, direccion, telefono_empresa, correo_empresa, nombre_contacto, telefono_contacto) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                    );
                    if ($stmt->execute([$nombre_empresa, $razon_social, $rfc, $giro, $descripcion, $carrera_afin, $perfil_alumno, $direccion, $telefono_empresa, $correo_empresa, $nombre_contacto, $telefono_contacto])) {
                        $response = ['success' => true, 'message' => 'Empresa agregada correctamente.'];
                    } else {
                        $response['message'] = 'No se pudo agregar la empresa.';
                    }
                } catch (PDOException $e) {
                    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
                }
            } else {
                $response['message'] = 'Por favor, complete todos los campos obligatorios.';
            }
            break;

        case 'edit_empresa':
            $id_empresa = $data['id_empresa'] ?? 0;
            // Recoger todos los campos del formulario
            $nombre_empresa = $data['nombre_empresa'] ?? '';
            $razon_social = $data['razon_social'] ?? null;
            $rfc = $data['rfc'] ?? null;
            $giro = $data['giro'] ?? null;
            $descripcion = $data['descripcion'] ?? '';
            $carrera_afin = $data['carrera_afin'] ?? '';
            $perfil_alumno = $data['perfil_alumno'] ?? null;
            $direccion = $data['direccion'] ?? '';
            $telefono_empresa = $data['telefono_empresa'] ?? '';
            $correo_empresa = $data['correo_empresa'] ?? '';
            $nombre_contacto = $data['nombre_contacto'] ?? null;
            $telefono_contacto = $data['telefono_contacto'] ?? null;

            if ($id_empresa > 0 && !empty($nombre_empresa) && !empty($descripcion)) {
                try {
                    $stmt = $pdo->prepare(
                        "UPDATE empresas SET 
                        nombre_empresa = ?, razon_social = ?, rfc = ?, giro = ?, descripcion = ?, 
                        carrera_afin = ?, perfil_alumno = ?, direccion = ?, telefono_empresa = ?, 
                        correo_empresa = ?, nombre_contacto = ?, telefono_contacto = ?
                        WHERE id_empresa = ?"
                    );
                    if ($stmt->execute([
                        $nombre_empresa, $razon_social, $rfc, $giro, $descripcion, $carrera_afin, 
                        $perfil_alumno, $direccion, $telefono_empresa, $correo_empresa, 
                        $nombre_contacto, $telefono_contacto, $id_empresa
                    ])) {
                        $response = ['success' => true, 'message' => 'Empresa actualizada correctamente.'];
                    } else {
                        $response['message'] = 'No se pudo actualizar la empresa.';
                    }
                } catch (PDOException $e) {
                    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
                }
            } else {
                $response['message'] = 'Datos inválidos para actualizar la empresa.';
            }
            break;

        case 'accept_vinculacion':
            $id_postulacion = $data['id_postulacion'] ?? 0;
            $fecha_ingreso = $data['fecha_ingreso'] ?? '';
            $fecha_egreso = $data['fecha_egreso'] ?? '';
            $puesto = $data['puesto'] ?? '';

            if ($id_postulacion > 0 && !empty($fecha_ingreso) && !empty($fecha_egreso) && !empty($puesto)) {
                try {
                    $stmt = $pdo->prepare(
                        "UPDATE registro_alumnos SET fecha_ingreso = ?, fecha_egreso = ?, puesto = ?, estatus = 'Aceptado' WHERE id_registro = ?"
                    );
                    if ($stmt->execute([$fecha_ingreso, $fecha_egreso, $puesto, $id_postulacion])) {
                        $response = ['success' => true, 'message' => 'Vinculación aceptada y actualizada correctamente.'];
                    } else {
                        $response['message'] = 'No se pudo actualizar la vinculación.';
                    }
                } catch (PDOException $e) {
                    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
                }
            } else {
                $response['message'] = 'Por favor, complete todos los campos.';
            }
            break;

        case 'delete_empresa':
            $id_empresa = $data['id_empresa'] ?? 0;
            if ($id_empresa > 0) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM empresas WHERE id_empresa = ?");
                    if ($stmt->execute([$id_empresa])) {
                        $response = ['success' => true, 'message' => 'Empresa eliminada correctamente.'];
                    } else {
                        $response['message'] = 'No se pudo eliminar la empresa.';
                    }
                } catch (PDOException $e) {
                    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
                }
            } else {
                $response['message'] = 'ID de empresa no válido.';
            }
            break;

        default:
            $response['message'] = 'Acción desconocida.';
            break;
    }
}

// Manejar solicitudes GET para obtener datos de una empresa
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_empresa') {
    $id_empresa = $_GET['id'] ?? 0;
    if ($id_empresa > 0) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM empresas WHERE id_empresa = ?");
            $stmt->execute([$id_empresa]);
            $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($empresa) {
                $response = ['success' => true, 'data' => $empresa];
            } else {
                $response['message'] = 'Empresa no encontrada.';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Error de base de datos: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'ID de empresa no válido.';
    }
}


echo json_encode($response);
?>
