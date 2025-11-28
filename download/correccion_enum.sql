-- Corrección de la codificación de caracteres en las definiciones ENUM.
-- Por favor, ejecute este script en su base de datos para arreglar los problemas con acentos.

-- Corregir la tabla de alumnos
ALTER TABLE `alumnos` MODIFY `carrera` 
ENUM('Ofimática','Programación','Construcción','Contabilidad','Ciencia de Datos') NOT NULL;

-- Corregir la tabla de empresas
ALTER TABLE `empresas` MODIFY `carrera_afin` 
ENUM('Ofimática','Programación','Construcción','Contabilidad','Ciencia de Datos') NOT NULL;
