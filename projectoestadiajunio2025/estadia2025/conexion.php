<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "sistema_cuentas";

$conexion = new mysqli($host, $user, $pass, $db);

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}
?>
