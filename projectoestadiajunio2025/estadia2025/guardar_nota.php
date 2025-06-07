<?php
session_start();
require 'conexion.php'; // conexión a sistema_cuentas

// Validar que el usuario esté logueado
if (!isset($_SESSION['usuario'])) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "sin_sesion"]);
    exit;
}

// Obtener datos del formulario
$input = json_decode(file_get_contents('php://input'), true);
$asunto = $input['asunto'] ?? '';
$descripcion = $input['descripcion'] ?? '';
$autor = $_SESSION['usuario']; // nombre del usuario logueado
$fecha_creacion = date("Y-m-d H:i:s");

// Validar datos
if (empty($asunto) || empty($descripcion)) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "datos_invalidos"]);
    exit;
}

// Preparar e insertar en la base de datos
$stmt = $conexion->prepare("INSERT INTO notas (asunto, descripcion, autor, fecha_creacion) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $asunto, $descripcion, $autor, $fecha_creacion);

if ($stmt->execute()) {
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "ok",
        "fecha_creacion" => $fecha_creacion,
        "id" => $conexion->insert_id
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "error",
        "message" => $stmt->error
    ]);
}
$stmt->close();
$conexion->close();