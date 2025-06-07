<?php
session_start();
require 'conexion.php';

$email = $_POST['email'];
$contrasena = $_POST['contrasena'];

// Buscar el usuario por email
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $usuario = $resultado->fetch_assoc();

    if ($contrasena === $usuario['contrasena']) {

        $_SESSION['usuario'] = $usuario['nombre']; // Guardas el nombre o ID
        header("Location: admin.php");
        exit;
    }
}

$_SESSION['error'] = "Email y/o contraseña incorrectos";
header("Location: login.php");
exit;
?>
