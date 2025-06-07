<?php
include "conexion.php";

$result = $conexion->query("SELECT * FROM notas ORDER BY fecha_creacion DESC");

$notas = [];
while ($row = $result->fetch_assoc()) {
    $notas[] = $row;
}

header('Content-Type: application/json');
echo json_encode($notas);
?>
