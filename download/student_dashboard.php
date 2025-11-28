 <?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    header('Location: index.html');
    exit();
}

require 'php/db_connection.php';

// Obtener información del alumno
$stmt = $pdo->prepare("SELECT * FROM alumnos WHERE id_alumno = ?");
$stmt->execute([$_SESSION['user_id']]);
$alumno = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar si el alumno ya está aceptado en una empresa
$stmt_check_inscripcion = $pdo->prepare("
    SELECT r.*, e.nombre_empresa, e.descripcion, e.carrera_afin 
    FROM registro_alumnos r 
    JOIN empresas e ON r.id_empresa = e.id_empresa 
    WHERE r.id_alumno = ? AND r.estatus = 'Aceptado'
");
$stmt_check_inscripcion->execute([$_SESSION['user_id']]);
$inscripcion_activa = $stmt_check_inscripcion->fetch(PDO::FETCH_ASSOC);

// Obtener empresas solo si no está inscrito
$empresas = [];
if (!$inscripcion_activa) {
    $empresas_stmt = $pdo->query("SELECT * FROM empresas ORDER BY nombre_empresa");
    $empresas = $empresas_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener postulaciones del alumno
$postulaciones_stmt = $pdo->prepare("
    SELECT r.fecha_registro, r.estatus, e.nombre_empresa 
    FROM registro_alumnos r
    JOIN empresas e ON r.id_empresa = e.id_empresa
    WHERE r.id_alumno = ?
    ORDER BY r.fecha_registro DESC
");
$postulaciones_stmt->execute([$_SESSION['user_id']]);
$postulaciones = $postulaciones_stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Alumno - CBTIS 258</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header class="dashboard-header">
        <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
        <nav>
            <a href="info_dual.html" class="btn">Info Empresa Dual</a>
            <a href="php/logout.php" class="btn btn-logout">Cerrar Sesión</a>
        </nav>
    </header>

    <main class="dashboard-container">
        <section class="profile-section card">
            <h2>Mi Perfil <i id="edit-profile-icon" class="fas fa-pencil-alt edit-icon" title="Editar Perfil"></i></h2>
            <div id="profile-display" class="profile-grid">
                <div class="profile-item">
                    <p><strong>Nombre Completo:</strong></p>
                    <span id="display-nombre"><?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['ap_paterno'] . ' ' . $alumno['ap_materno']); ?></span>
                </div>
                <div class="profile-item">
                    <p><strong>Carrera:</strong></p>
                    <span id="display-carrera"><?php echo htmlspecialchars($alumno['carrera']); ?></span>
                </div>
                <div class="profile-item">
                    <p><strong>Correo:</strong></p>
                    <span id="display-correo"><?php echo htmlspecialchars($alumno['correo_electronico']); ?></span>
                </div>
                 <div class="profile-item">
                    <p><strong>Semestre:</strong></p>
                    <span id="display-semestre"><?php echo htmlspecialchars($alumno['semestre']); ?></span>
                </div>
                <div class="profile-item">
                    <p><strong>Teléfono:</strong></p>
                    <span id="display-telefono"><?php echo htmlspecialchars($alumno['telefono']); ?></span>
                </div>
                <div class="profile-item">
                    <p><strong>Dirección:</strong></p>
                    <span id="display-direccion"><?php echo htmlspecialchars($alumno['direccion']); ?></span>
                </div>
            </div>
        </section>

        <!-- Modal para Editar Perfil -->
        <div id="profile-modal" class="modal" style="display: none;">
            <div class="modal-content">
                <span id="close-modal-button" class="close-button">&times;</span>
                <h2>Editar Mi Perfil</h2>
                <form id="profile-edit-form">
                    <!-- Campos del formulario -->
                    <div class="form-group">
                        <label for="nombre">Nombre(s):</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($alumno['nombre']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="ap_paterno">Apellido Paterno:</label>
                        <input type="text" id="ap_paterno" name="ap_paterno" value="<?php echo htmlspecialchars($alumno['ap_paterno']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="ap_materno">Apellido Materno:</label>
                        <input type="text" id="ap_materno" name="ap_materno" value="<?php echo htmlspecialchars($alumno['ap_materno']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="correo_electronico">Correo Electrónico:</label>
                        <input type="email" id="correo_electronico" name="correo_electronico" value="<?php echo htmlspecialchars($alumno['correo_electronico']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($alumno['telefono']); ?>" required>
                    </div>
                     <div class="form-group">
                        <label for="direccion">Dirección:</label>
                        <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($alumno['direccion']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="contrasena">Nueva Contraseña (dejar en blanco para no cambiar):</label>
                        <input type="password" id="contrasena" name="contrasena">
                    </div>
                    <div class="form-actions">
                        <button type="button" id="cancel-edit-btn" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" id="save-profile-btn" class="btn" disabled>Guardar Cambios</button>
                    </div>
                    <div id="form-feedback" class="form-feedback" style="display: none;"></div>
                </form>
            </div>
        </div>

        <!-- Modal para Confirmar Solicitud -->
        <div id="confirm-modal" class="modal" style="display: none;">
            <div class="modal-content">
                <h2>Confirmar Solicitud</h2>
                <p>¿Estás seguro de que quieres enviar tu solicitud para unirte a <strong id="empresa-a-unirse"></strong>?</p>
                <div class="form-actions">
                    <button type="button" id="cancel-solicitud-btn" class="btn btn-secondary">Cancelar</button>
                    <button type="button" id="confirm-solicitud-btn" class="btn">Confirmar</button>
                </div>
            </div>
        </div>

        <!-- Modal para Confirmar Solicitud de Baja -->
        <div id="confirm-baja-modal" class="modal" style="display: none;">
            <div class="modal-content">
                <h2>Confirmar Solicitud de Baja</h2>
                <p>¿Estás seguro de que quieres solicitar tu baja de la empresa? Tu solicitud será enviada al administrador para su revisión y aprobación.</p>
                <div class="form-actions">
                    <button type="button" id="cancel-baja-btn" class="btn btn-secondary">Cancelar</button>
                    <button type="button" id="confirm-baja-btn" class="btn">Confirmar Solicitud</button>
                </div>
            </div>
        </div>

        <section class="empresas-section card">
            <?php if ($inscripcion_activa): ?>
                <h2>Mi Empresa Actual</h2>
                <div class="empresa-item">
                    <h3><?php echo htmlspecialchars($inscripcion_activa['nombre_empresa']); ?></h3>
                    <p><strong>Descripción:</strong> <?php echo nl2br(htmlspecialchars($inscripcion_activa['descripcion'])); ?></p>
                    <p><strong>Carrera Afín:</strong> <?php echo htmlspecialchars($inscripcion_activa['carrera_afin']); ?></p>
                    <button class="btn btn-baja" data-id-registro="<?php echo $inscripcion_activa['id_registro']; ?>">Solicitar darme de baja</button>
                </div>
            <?php else: ?>
                <h2>Empresas Disponibles</h2>
                <div id="empresas-list">
                    <?php if (count($empresas) > 0): ?>
                        <?php foreach ($empresas as $empresa): ?>
                            <div class="empresa-item">
                                <h3><?php echo htmlspecialchars($empresa['nombre_empresa']); ?></h3>
                                <p><strong>Descripción:</strong> <?php echo nl2br(htmlspecialchars($empresa['descripcion'])); ?></p>
                                <p><strong>Carrera Afín:</strong> <?php echo htmlspecialchars($empresa['carrera_afin']); ?></p>
                                <button class="btn btn-solicitar" data-id-empresa="<?php echo $empresa['id_empresa']; ?>" data-nombre-empresa="<?php echo htmlspecialchars($empresa['nombre_empresa']); ?>">Mandar Solicitud</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No hay empresas disponibles en este momento.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="postulaciones-section card">
            <h2>Mis Postulaciones</h2>
            <div id="postulaciones-list">
                 <?php if (count($postulaciones) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>Fecha de Postulación</th>
                                <th>Estatus</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($postulaciones as $postulacion): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($postulacion['nombre_empresa']); ?></td>
                                <td><?php echo date("d/m/Y H:i", strtotime($postulacion['fecha_registro'])); ?></td>
                                <td><span class="status status-<?php echo strtolower(htmlspecialchars($postulacion['estatus'])); ?>"><?php echo htmlspecialchars($postulacion['estatus']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Aún no te has postulado a ninguna empresa.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Contenedor para las notificaciones (Toast) -->
    <div id="notification-container"></div>
    
    <script src="js/dashboard_student.js"></script>
</body>
</html>
