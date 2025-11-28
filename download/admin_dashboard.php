<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.html');
    exit();
}

require 'php/db_connection.php';

// Para simplificar, obtenemos todos los datos en una carga inicial.
// En una app más grande, se usaría paginación y AJAX para cargar datos.

// Reporte de Alumnos con detalles de vinculación
$reporte_alumnos = $pdo->query("
    SELECT 
        a.id_alumno, a.nombre, a.ap_paterno, a.ap_materno,
        a.carrera, a.semestre,
        e.nombre_empresa,
        r.puesto, r.fecha_ingreso, r.fecha_egreso, r.id_registro
    FROM alumnos a
    LEFT JOIN registro_alumnos r ON a.id_alumno = r.id_alumno AND r.estatus = 'Aceptado'
    LEFT JOIN empresas e ON r.id_empresa = e.id_empresa
    ORDER BY a.ap_paterno
")->fetchAll(PDO::FETCH_ASSOC);

// Reporte de Empresas
$reporte_empresas = $pdo->query("SELECT * FROM empresas ORDER BY nombre_empresa")->fetchAll(PDO::FETCH_ASSOC);

// Reporte de Usuarios
$reporte_usuarios = $pdo->query("
    SELECT nombre, ap_paterno, ap_materno, correo_electronico, telefono, direccion, n_control, carrera, semestre, grupo, turno 
    FROM alumnos 
    ORDER BY ap_paterno
")->fetchAll(PDO::FETCH_ASSOC);

// Gestión de Vinculaciones
$postulaciones = $pdo->query("
    SELECT r.id_registro, r.fecha_registro, r.estatus, 
           a.nombre, a.ap_paterno, 
           e.nombre_empresa
    FROM registro_alumnos r
    JOIN alumnos a ON r.id_alumno = a.id_alumno
    JOIN empresas e ON r.id_empresa = e.id_empresa
    ORDER BY 
        CASE 
            WHEN r.estatus = 'Pendiente' THEN 1
            WHEN r.estatus = 'Baja Solicitada' THEN 2
            ELSE 3
        END,
        r.fecha_registro DESC
")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/admin_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
</head>
<body>
    <header class="dashboard-header">
        <h1>Panel de Administrador</h1>
        <nav>
            <a href="php/logout.php" class="btn btn-logout">Cerrar Sesión</a>
        </nav>
    </header>

    <main class="dashboard-container">
        <div class="admin-panel">
            <!-- Pestañas de Navegación -->
            <div class="tabs">
                <button class="tab-link active" data-target="usuarios"><i class="fas fa-users"></i> Usuarios</button>
                <button class="tab-link" data-target="gestion-alumnos"><i class="fas fa-user-graduate"></i> Gestión de Alumnos</button>
                <button class="tab-link" data-target="gestion-empresas"><i class="fas fa-building"></i> Gestión de Empresas</button>
                <button class="tab-link" data-target="gestion-vinculaciones"><i class="fas fa-handshake"></i> Gestión de Vinculaciones</button>
            </div>

            <!-- Contenedor para el Contenido de las Pestañas -->
            <div class="tab-content-container">
                <div id="usuarios" class="tab-content active">
                    <div class="card">
                        <h2>Usuarios Registrados (<?php echo count($reporte_usuarios); ?>)</h2>
                        <button class="btn btn-report" data-table="usuarios-table">Generar Reporte PDF</button>
                        <div class="table-responsive">
                            <table id="usuarios-table">
                                <thead>
                                    <tr>
                                        <th>Nombre Completo</th>
                                        <th>Correo</th>
                                        <th>Teléfono</th>
                                        <th>Dirección</th>
                                        <th>N° Control</th>
                                        <th>Carrera</th>
                                        <th>Semestre</th>
                                        <th>Grupo</th>
                                        <th>Turno</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reporte_usuarios as $usuario): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['ap_paterno'] . ' ' . $usuario['ap_materno']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['correo_electronico']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['telefono']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['direccion']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['n_control']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['carrera']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['semestre']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['grupo']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['turno']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="gestion-alumnos" class="tab-content">
                    <div class="card">
                        <h2>Gestión de Alumnos (<?php echo count($reporte_alumnos); ?>)</h2>
                        <button class="btn btn-report" data-table="alumnos-table">Generar Reporte PDF</button>
                <div class="table-responsive">
                <table id="alumnos-table">
                    <thead>
                        <tr>
                            <th>ID Alumno</th>
                            <th>Nombre Completo</th>
                            <th>Carrera</th>
                            <th>Semestre</th>
                            <th>Empresa</th>
                            <th>Puesto</th>
                            <th>Fecha Ingreso</th>
                            <th>Fecha Egreso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reporte_alumnos as $alumno): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($alumno['id_alumno']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['ap_paterno'] . ' ' . $alumno['ap_materno']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['carrera']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['semestre']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['nombre_empresa'] ?? 'N/A'); ?></td>
                            <td>
                                <input type="text" class="editable-field" data-id="<?php echo $alumno['id_registro']; ?>" data-field="puesto" value="<?php echo htmlspecialchars($alumno['puesto'] ?? ''); ?>" <?php echo !$alumno['id_registro'] ? 'disabled' : ''; ?>>
                            </td>
                            <td class="date-cell">
                                <div class="date-container">
                                    <span class="date-display">
                                        <?php if (!empty($alumno['fecha_ingreso'])): ?>
                                            <?php echo htmlspecialchars(date("d/m/Y", strtotime($alumno['fecha_ingreso']))); ?>
                                        <?php else: ?>
                                            <span class="edit-icon">&#9998;</span>
                                        <?php endif; ?>
                                    </span>
                                    <input type="date" class="editable-field date-input" 
                                           data-id="<?php echo $alumno['id_registro']; ?>" 
                                           data-field="fecha_ingreso" 
                                           value="<?php echo htmlspecialchars($alumno['fecha_ingreso'] ?? ''); ?>" 
                                           style="display: none;" 
                                           <?php echo !$alumno['id_registro'] ? 'disabled' : ''; ?>>
                                </div>
                            </td>
                            <td class="date-cell">
                                <div class="date-container">
                                    <span class="date-display">
                                        <?php if (!empty($alumno['fecha_egreso'])): ?>
                                            <?php echo htmlspecialchars(date("d/m/Y", strtotime($alumno['fecha_egreso']))); ?>
                                        <?php else: ?>
                                            <span class="edit-icon">&#9998;</span>
                                        <?php endif; ?>
                                    </span>
                                    <input type="date" class="editable-field date-input" 
                                           data-id="<?php echo $alumno['id_registro']; ?>" 
                                           data-field="fecha_egreso" 
                                           value="<?php echo htmlspecialchars($alumno['fecha_egreso'] ?? ''); ?>" 
                                           style="display: none;" 
                                           <?php echo !$alumno['id_registro'] ? 'disabled' : ''; ?>>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <div id="gestion-empresas" class="tab-content">
            <div class="card">
                <h2>Gestión de Empresas (<?php echo count($reporte_empresas); ?>)</h2>
                <button id="add-empresa-btn" class="btn btn-add">Agregar Nueva Empresa</button>
                <button class="btn btn-report" data-table="empresas-table">Generar Reporte PDF</button>
                <div class="table-responsive">
                <table id="empresas-table">
                   <thead>
                        <tr>
                            <th>ID</th>
                            <th>Razón Social</th>
                            <th>RFC</th>
                            <th>Giro</th>
                            <th>Dirección</th>
                            <th>Contacto</th>
                            <th>Tel. Contacto</th>
                            <th>Perfil Alumno</th>
                            <th>Página Info</th>
                            <th>Dual Prog.</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reporte_empresas as $empresa): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($empresa['id_empresa']); ?></td>
                            <td><?php echo htmlspecialchars($empresa['razon_social'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($empresa['rfc'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($empresa['giro'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($empresa['direccion'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($empresa['nombre_contacto'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($empresa['telefono_contacto'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($empresa['perfil_alumno'] ?? ''); ?></td>
                            <td><a href="<?php echo htmlspecialchars($empresa['pagina_informativa'] ?? ''); ?>" target="_blank">Link</a></td>
                            <td><?php echo htmlspecialchars($empresa['empresa_dual_programacion'] ?? 'No'); ?></td>
                            <td>
                                <button class="btn-action btn-edit" data-id="<?php echo $empresa['id_empresa']; ?>">Editar</button>
                                <button class="btn-action btn-delete" data-id="<?php echo $empresa['id_empresa']; ?>">Eliminar</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <div id="gestion-vinculaciones" class="tab-content">
            <div class="card">
                <h2>Gestión de Vinculaciones (<?php echo count($postulaciones); ?>)</h2>
                <button class="btn btn-report" data-table="vinculaciones-table">Generar Reporte PDF</button>
                <table id="vinculaciones-table">
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Empresa</th>
                            <th>Fecha</th>
                            <th>Estatus</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($postulaciones as $post): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($post['nombre'] . ' ' . $post['ap_paterno']); ?></td>
                            <td><?php echo htmlspecialchars($post['nombre_empresa']); ?></td>
                            <td><?php echo date("d/m/Y", strtotime($post['fecha_registro'])); ?></td>
                            <td>
                                <span class="status status-<?php echo strtolower(str_replace(' ', '-', htmlspecialchars($post['estatus']))); ?>">
                                    <?php echo htmlspecialchars($post['estatus']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (in_array($post['estatus'], ['Pendiente', 'Aceptado', 'Rechazado'])): ?>
                                    <select class="status-select" data-id="<?php echo $post['id_registro']; ?>">
                                        <option value="Pendiente" <?php echo $post['estatus'] === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="Aceptado" <?php echo $post['estatus'] === 'Aceptado' ? 'selected' : ''; ?>>Aceptado</option>
                                        <option value="Rechazado" <?php echo $post['estatus'] === 'Rechazado' ? 'selected' : ''; ?>>Rechazado</option>
                                    </select>
                                <?php elseif (in_array($post['estatus'], ['Baja Solicitada', 'Baja Aceptada', 'Baja Rechazada'])): ?>
                                    <select class="status-select" data-id="<?php echo $post['id_registro']; ?>">
                                        <option value="Baja Solicitada" <?php echo $post['estatus'] === 'Baja Solicitada' ? 'selected' : ''; ?>>Baja Solicitada</option>
                                        <option value="Baja Aceptada" <?php echo $post['estatus'] === 'Baja Aceptada' ? 'selected' : ''; ?>>Baja Aceptada</option>
                                        <option value="Baja Rechazada" <?php echo $post['estatus'] === 'Baja Rechazada' ? 'selected' : ''; ?>>Baja Rechazada</option>
                                    </select>
                                <?php endif; ?>
                                <?php if ($post['estatus'] === 'Pendiente'): ?>
                                    <button class="btn-action btn-aceptar" data-id="<?php echo $post['id_registro']; ?>">Aceptar</button>
                                    <button class="btn-action btn-denegar" data-id="<?php echo $post['id_registro']; ?>">Denegar</button>
                                <?php endif; ?>
                                <button class="btn-action btn-delete-postulacion" data-id="<?php echo $post['id_registro']; ?>">Eliminar</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        </div> <!-- Cierre de tab-content-container -->
    </div> <!-- Cierre de admin-panel -->
    </main>

    <!-- Modal para Agregar/Editar Empresa -->
    <div id="empresa-modal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2 id="modal-title">Agregar Nueva Empresa</h2>
            <form id="empresa-form">
                <input type="hidden" id="id_empresa" name="id_empresa">
                <div class="form-group">
                    <label for="nombre_empresa">Nombre Comercial:</label>
                    <input type="text" id="nombre_empresa" name="nombre_empresa" required>
                </div>
                 <div class="form-group">
                    <label for="razon_social">Razón Social:</label>
                    <input type="text" id="razon_social" name="razon_social">
                </div>
                <div class="form-group">
                    <label for="rfc">RFC:</label>
                    <input type="text" id="rfc" name="rfc">
                </div>
                 <div class="form-group">
                    <label for="giro">Giro:</label>
                    <input type="text" id="giro" name="giro">
                </div>
                 <div class="form-group">
                    <label for="descripcion">Descripción de la Empresa y Actividades:</label>
                    <textarea id="descripcion" name="descripcion" required></textarea>
                </div>
                 <div class="form-group">
                    <label for="carrera_afin">Carrera Afín:</label>
                    <select id="carrera_afin" name="carrera_afin" required>
                        <option value="Ofimática">Ofimática</option>
                        <option value="Programación">Programación</option>
                        <option value="Construcción">Construcción</option>
                        <option value="Contabilidad">Contabilidad</option>
                        <option value="Ciencia de Datos">Ciencia de Datos</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="perfil_alumno">Perfil del Alumno Requerido:</label>
                    <textarea id="perfil_alumno" name="perfil_alumno"></textarea>
                </div>
                <div class="form-group">
                    <label for="direccion">Dirección de la Empresa:</label>
                    <input type="text" id="direccion" name="direccion" required>
                </div>
                <div class="form-group">
                    <label for="telefono_empresa">Teléfono de la Empresa:</label>
                    <input type="text" id="telefono_empresa" name="telefono_empresa" required>
                </div>
                <div class="form-group">
                    <label for="correo_empresa">Correo de la Empresa:</label>
                    <input type="email" id="correo_empresa" name="correo_empresa" required>
                </div>
                <div class="form-group">
                    <label for="nombre_contacto">Nombre del Contacto:</label>
                    <input type="text" id="nombre_contacto" name="nombre_contacto">
                </div>
                 <div class="form-group">
                    <label for="telefono_contacto">Teléfono del Contacto:</label>
                    <input type="text" id="telefono_contacto" name="telefono_contacto">
                </div>
                <button type="submit" class="btn">Guardar Empresa</button>
            </form>
        </div>
    </div>

    <script src="js/admin_dashboard.js"></script>

    <!-- Modal para Aceptar Vinculación -->
    <div id="aceptar-vinculacion-modal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2 id="modal-title">Aceptar Vinculación</h2>
            <form id="aceptar-vinculacion-form">
                <input type="hidden" id="id_registro_vinculacion" name="id_registro">
                <div class="form-group">
                    <label for="fecha_ingreso">Fecha de Ingreso:</label>
                    <input type="date" id="fecha_ingreso" name="fecha_ingreso" required>
                </div>
                <div class="form-group">
                    <label for="fecha_egreso">Fecha de Egreso:</label>
                    <input type="date" id="fecha_egreso" name="fecha_egreso" required>
                </div>
                <div class="form-group">
                    <label for="puesto">Puesto:</label>
                    <input type="text" id="puesto" name="puesto" required>
                </div>
                <button type="submit" class="btn">Guardar</button>
            </form>
        </div>
    </div>
</body>
</html>
