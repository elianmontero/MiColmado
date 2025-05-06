<?php
include '../public/config.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['estado'])) {
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
    exit();
}

$idPedido = intval($_GET['id']);
$estado = $_GET['estado'];

// Validar el estado
$estadosValidos = ['pendiente', 'procesado', 'entregado', 'cancelado'];
if (!in_array($estado, $estadosValidos)) {
    echo json_encode(['success' => false, 'message' => 'Estado inválido.']);
    exit();
}

// Actualizar el estado del pedido
$stmt = $conn->prepare("UPDATE pedido SET estado = ? WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta: ' . $conn->error]);
    exit();
}

$stmt->bind_param("si", $estado, $idPedido);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado del pedido: ' . $stmt->error]);
}
$stmt->close();
?>