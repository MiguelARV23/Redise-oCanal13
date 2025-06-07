<?php
include 'conexion.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tipo = $_GET['tipo'] ?? '';

if ($id <= 0 || !in_array($tipo, ['noticia', 'nota'])) {
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit;
}

if ($tipo === 'noticia') {
    $sql = "SELECT id, titulo, descripcion, fecha_creacion, categorias, estado, imagen FROM noticias WHERE id = ?";
} else {
    $sql = "SELECT id, asunto, descripcion, fecha_creacion, autor FROM notas WHERE id = ?";
}

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($fila = $resultado->fetch_assoc()) {
    if (isset($fila['imagen']) && !empty($fila['imagen'])) {
        $fila['imagen'] = base64_encode($fila['imagen']);
    }
    echo json_encode($fila);
} else {
    echo json_encode(["error" => "No encontrado"]);
}
?>
