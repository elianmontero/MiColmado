<?php
include '../public/config.php';
session_start();

// Verificar si hay una sesión activa
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403); // Prohibido
    echo "No tienes permiso para realizar esta acción.";
    exit();
}

// Verificar si se recibió el ID del producto
if (isset($_POST['id'])) {
    $producto_id = intval($_POST['id']);

    // Eliminar el producto de la base de datos
    $stmt = $conn->prepare("DELETE FROM producto WHERE id = ?");
    $stmt->bind_param("i", $producto_id);

    if ($stmt->execute()) {
        http_response_code(200); // Éxito
        echo "Producto eliminado correctamente.";
    } else {
        http_response_code(500); // Error interno del servidor
        echo "Error al eliminar el producto.";
    }

    $stmt->close();
} else {
    http_response_code(400); // Solicitud incorrecta
    echo "ID de producto no válido.";
}

$conn->close();
?>