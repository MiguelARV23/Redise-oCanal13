<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    http_response_code(403);
    echo "Acceso denegado.";
    exit;
}

include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titulo = $_POST["titulo"] ?? '';
    $descripcion = $_POST["descripcion"] ?? '';
    $estado = $_POST["estado"] ?? '';
    $categoria = $_POST["categoria"] ?? '';
    $autor = $_SESSION["usuario"];
    $imagenBinaria = null;

    // Validar y procesar imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagenBinaria = file_get_contents($_FILES['imagen']['tmp_name']);
    }

    // CAMBIO: Preparar y ejecutar consulta
    $stmt = $conexion->prepare("INSERT INTO noticias (titulo, autor, categoria, estado, descripcion, imagen) VALUES (?, ?, ?, ?, ?, ?)");

    $nulo = null; // CAMBIO: se usa como placeholder para el blob en bind_param

    // CAMBIO: bind_param con variable dummy para imagen
    $stmt->bind_param("sssssb", $titulo, $autor, $categoria, $estado, $descripcion, $nulo);

    if ($imagenBinaria !== null) {
        // CAMBIO: Enviar imagen binaria como datos largos al índice 5 (posición del blob)
        $stmt->send_long_data(5, $imagenBinaria);
    }

    if ($stmt->execute()) {
        echo "Noticia guardada exitosamente.";
    } else {
        http_response_code(500);
        echo "Error al guardar la noticia: " . $stmt->error;
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo "Método no permitido.";
}

$conexion->close();
?>
