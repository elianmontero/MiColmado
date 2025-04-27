<?php
include 'conectar.php';

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'];
$nombre = $data['nombre'];
$marca = $data['marca'];
$categorias = $data['categorias'];
$imagen_url = $data['imagen_url'];
$pais = "República Dominicana";
$precio = isset($data['precio']) ? $data['precio'] : 'N/A';

$sql = "INSERT INTO productos (id, nombre, marca, categorias, imagen_url, pais, precio) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), marca=VALUES(marca), precio=VALUES(precio)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $id, $nombre, $marca, $categorias, $imagen_url, $pais, $precio);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => $conn->error]);
}

$stmt->close();
$conn->close();
?>