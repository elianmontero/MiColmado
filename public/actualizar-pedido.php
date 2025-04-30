<?php
include '../public/config.php';
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'proveedor') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit();
}

if (isset($_GET['id']) && isset($_GET['estado'])) {
    $pedido_id = intval($_GET['id']);
    $estado = $_GET['estado'];

    $stmt = $conn->prepare("UPDATE pedido SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $estado, $pedido_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Estado del pedido actualizado.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado del pedido.']);
    }
    $stmt->close();
    exit();
}

echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
?>