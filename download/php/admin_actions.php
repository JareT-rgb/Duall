<?php
session_start();
require 'db_connection.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Acciรณn no vรกlida.'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $response['message'] = 'No tienes permiso para realizar esta acciรณn.';
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
            
            // Validar los posibles estatus
            $allowed_statuses = ['Pendiente', 'Aceptado', 'Rechazado', 'Baja Aceptada', 'Baja Rechazada'];

            if ($id_registro > 0 && in_array($estatus, $allowed_statuses)) {
                try {
                    $sql = "UPDATE registro_alumnos SET estatus = ?";
                    $params = [$estatus];

                    // Si se rechaza, registrar la fecha para la regla de 12 horas
                    if ($estatus === 'Rechazado') {
                        $sql .= ", fecha_rechazo = NOW()";
                    }

                    // Si se acepta una baja, se podría limpiar la información de la empresa.
                    if ($estatus === 'Baja Aceptada') {
                         // Opcional: Anular datos de la vinculación activa
                        $sql .= ", fecha_ingreso = NULL, fecha_egreso = NULL, puesto = NULL";
                    }

                    // Si se rechaza una baja, simplemente se actualiza el estatus. No se tocan las fechas.
                    // El estatus 'Aceptado' se reserva para la acción de aceptar una postulación 'Pendiente'.
                    if ($estatus === 'Baja Rechazada') {
                        // El estatus vuelve a ser 'Aceptado' porque la baja no procedió.
                        $estatus = 'Aceptado';
                        $sql = "UPDATE registro_alumnos SET estatus = ?";
                    }
                    
                    $sql .= " WHERE id_registro = ?";
                    $params[] = $id_registro;

                    $stmt = $pdo->prepare($sql);

                    if ($stmt->execute($params)) {
                        $response = ['success' => true, 'message' => 'Estatus actualizado correctamente.'];
                    } else {
                        $response['message'] = 'No se pudo actualizar el estatus.';
                    }
                } catch (PDOException $e) {
                    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
                }
            } else {
                $response['message'] = 'Datos inválidos para actualizar el estatus.';
            }
            break;

        case 'update_alumno_report':
            $id_registro = $data['id_registro'] ?? 0;
            $field = $data['field'] ?? '';
            $value = $data['value'] ?? '';

            if ($id_registro > 0 && in_array($field, ['puesto', 'fecha_ingreso', 'fecha_egreso'])) {
                try {
                    // Si el valor de fecha estรก vacรญo, guardarlo como NULL
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
                $response['message'] = 'Datos invรกlidos para actualizar el reporte.';
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
                $response['message'] = 'Datos invรกlidos para actualizar la empresa.';
            }
            break;

        case 'accept_vinculacion':
            $id_registro = $data['id_registro'] ?? 0;
            $fecha_ingreso = $data['fecha_ingreso'] ?? '';
            $fecha_egreso = $data['fecha_egreso'] ?? '';
            $puesto = $data['puesto'] ?? '';

            if ($id_registro > 0 && !empty($fecha_ingreso) && !empty($fecha_egreso) && !empty($puesto)) {
                try {
                    // Primero, obtenemos el id_alumno de este registro
                    $stmt_get_alumno = $pdo->prepare("SELECT id_alumno FROM registro_alumnos WHERE id_registro = ?");
                    $stmt_get_alumno->execute([$id_registro]);
                    $id_alumno = $stmt_get_alumno->fetchColumn();

                    if ($id_alumno) {
                        // Anular cualquier otra postulación 'Aceptada' del mismo alumno
                        $stmt_anular = $pdo->prepare("UPDATE registro_alumnos SET estatus = 'Anulado' WHERE id_alumno = ? AND estatus = 'Aceptado'");
                        $stmt_anular->execute([$id_alumno]);

                        // Ahora, actualizamos la postulación actual como 'Aceptado'
                        $stmt_aceptar = $pdo->prepare(
                            "UPDATE registro_alumnos SET fecha_ingreso = ?, fecha_egreso = ?, puesto = ?, estatus = 'Aceptado' WHERE id_registro = ?"
                        );
                        if ($stmt_aceptar->execute([$fecha_ingreso, $fecha_egreso, $puesto, $id_registro])) {
                            $response = ['success' => true, 'message' => 'Vinculación aceptada y actualizada correctamente.'];
                        } else {
                            $response['message'] = 'No se pudo actualizar la vinculación.';
                        }
                    } else {
                         $response['message'] = 'No se encontró el alumno asociado a este registro.';
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
                $response['message'] = 'ID de empresa no vรกlido.';
            }
            break;

        case 'edit_user':
            $n_control = $data['n_control'] ?? 0;
            $nombre = $data['nombre'] ?? '';
            $ap_paterno = $data['ap_paterno'] ?? '';
            $ap_materno = $data['ap_materno'] ?? '';
            $correo_electronico = $data['correo_electronico'] ?? '';
            $telefono = $data['telefono'] ?? '';
            $direccion = $data['direccion'] ?? '';
            $carrera = $data['carrera'] ?? '';
            $semestre = $data['semestre'] ?? 0;
            $grupo = $data['grupo'] ?? '';
            $turno = $data['turno'] ?? '';

            if ($n_control > 0 && !empty($nombre) && !empty($ap_paterno) && !empty($ap_materno)) {
                try {
                    $stmt = $pdo->prepare(
                        "UPDATE alumnos SET 
                        nombre = ?, ap_paterno = ?, ap_materno = ?, correo_electronico = ?, 
                        telefono = ?, direccion = ?, carrera = ?, semestre = ?, grupo = ?, turno = ?
                        WHERE n_control = ?"
                    );
                    if ($stmt->execute([
                        $nombre, $ap_paterno, $ap_materno, $correo_electronico, $telefono,
                        $direccion, $carrera, $semestre, $grupo, $turno, $n_control
                    ])) {
                        $response = ['success' => true, 'message' => 'Usuario actualizado correctamente.'];
                    } else {
                        $response['message'] = 'No se pudo actualizar el usuario.';
                    }
                } catch (PDOException $e) {
                    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
                }
            } else {
                $response['message'] = 'Datos inválidos para actualizar el usuario.';
            }
            break;

        case 'delete_user':
            $n_control = $data['n_control'] ?? 0;
            if ($n_control > 0) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM alumnos WHERE n_control = ?");
                    if ($stmt->execute([$n_control])) {
                        $response = ['success' => true, 'message' => 'Usuario eliminado correctamente.'];
                    } else {
                        $response['message'] = 'No se pudo eliminar el usuario.';
                    }
                } catch (PDOException $e) {
                    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
                }
            } else {
                $response['message'] = 'Número de control no válido.';
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

// Manejar solicitudes GET para obtener datos de un usuario
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_user') {
    $n_control = $_GET['id'] ?? 0;
    if ($n_control > 0) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM alumnos WHERE n_control = ?");
            $stmt->execute([$n_control]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $response = ['success' => true, 'data' => $user];
            } else {
                $response['message'] = 'Usuario no encontrado.';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Error de base de datos: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Número de control no válido.';
    }
}

echo json_encode($response);
?>
