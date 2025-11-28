<?php
$db_host = 'sql300.infinityfree.com';
$db_user = 'if0_40386948';
$db_pass = 'jaretjaja777';
$db_name = 'if0_40386948_vinculacion_nd';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // En un entorno de producción, no mostrarías el error detallado.
    // Lo registrarías en un archivo de log.
    die("Error: No se pudo conectar a la base de datos. " . $e->getMessage());
}
?>
