<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Eliminar el producto
    $delete_stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
    $delete_stmt->bind_param("i", $id);

    if ($delete_stmt->execute()) {
        echo "<p>Producto eliminado con éxito.</p>";
    } else {
        echo "<p>Error al eliminar el producto.</p>";
    }
}
?>
