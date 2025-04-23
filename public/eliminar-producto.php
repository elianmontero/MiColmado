<?php
include '../public/config.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo "No autorizado";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $producto_id = intval($_GET['id']);
    $usuario_id = $_SESSION['usuario_id'];

    // Verificar que el producto pertenece al usuario
    $stmt = $conn->prepare("SELECT p.id FROM producto p 
                            INNER JOIN colmado c ON p.id_colmado = c.id 
                            WHERE p.id = ? AND c.id_usuario = ?");
    $stmt->bind_param("ii", $producto_id, $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // Eliminar el producto
        $stmt = $conn->prepare("DELETE FROM producto WHERE id = ?");
        $stmt->bind_param("i", $producto_id);
        if ($stmt->execute()) {
            http_response_code(200);
            echo "Producto eliminado correctamente";
        } else {
            http_response_code(500);
            echo "Error al eliminar el producto";
        }
    } else {
        http_response_code(403);
        echo "No autorizado para eliminar este producto";
    }
} else {
    http_response_code(400);
    echo "Solicitud inválida";
}
?>