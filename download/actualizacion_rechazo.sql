-- Añadir la columna para registrar la fecha del rechazo
ALTER TABLE `registro_alumnos` ADD `fecha_rechazo` DATETIME NULL DEFAULT NULL;
