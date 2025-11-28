-- Actualizaciones para la tabla `empresas`
ALTER TABLE `empresas`
ADD COLUMN `razon_social` VARCHAR(255) DEFAULT NULL AFTER `nombre_empresa`,
ADD COLUMN `rfc` VARCHAR(13) DEFAULT NULL AFTER `razon_social`,
ADD COLUMN `giro` VARCHAR(255) DEFAULT NULL AFTER `rfc`,
ADD COLUMN `contacto` VARCHAR(255) DEFAULT NULL AFTER `correo_empresa`,
ADD COLUMN `telefono_contacto` VARCHAR(15) DEFAULT NULL AFTER `contacto`,
ADD COLUMN `perfil_alumno` TEXT DEFAULT NULL AFTER `telefono_contacto`,
ADD COLUMN `pagina_informativa` VARCHAR(255) DEFAULT NULL AFTER `perfil_alumno`,
ADD COLUMN `empresa_dual_programacion` VARCHAR(2) DEFAULT 'No' AFTER `pagina_informativa`;

-- Actualizaciones para la tabla `registro_alumnos`
ALTER TABLE `registro_alumnos`
ADD COLUMN `puesto` VARCHAR(100) DEFAULT NULL AFTER `id_empresa`,
ADD COLUMN `fecha_ingreso` DATE DEFAULT NULL AFTER `estatus`,
ADD COLUMN `fecha_egreso` DATE DEFAULT NULL AFTER `fecha_ingreso`,
ADD COLUMN `fecha_rechazo` DATETIME DEFAULT NULL AFTER `fecha_egreso`;
