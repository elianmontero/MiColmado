<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Obtener los datos del producto
    $stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $producto = $result->fetch_assoc();

    if (isset($_POST['actualizar_producto'])) {
        // Obtener los nuevos datos del formulario
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];

        // Actualizar el producto
        $update_stmt = $conn->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ? WHERE id = ?");
        $update_stmt->bind_param("ssdis", $nombre, $descripcion, $precio, $stock, $id);

        if ($update_stmt->execute()) {
            echo "<p>Producto actualizado con éxito.</p>";
        } else {
            echo "<p>Error al actualizar el producto.</p>";
        }
    }
}
?>

<h3>Editar Producto</h3>
<form action="editar_producto.php?id=<?php echo $producto['id']; ?>" method="POST">
    <label>Nombre:</label>
    <input type="text" name="nombre" value="<?php echo $producto['nombre']; ?>" required><br>

    <label>Descripción:</label>
    <textarea name="descripcion" required><?php echo $producto['descripcion']; ?></textarea><br>

    <label>Precio:</label>
    <input type="number" step="0.01" name="precio" value="<?php echo $producto['precio']; ?>" required><br>

    <label>Stock:</label>
    <input type="number" name="stock" value="<?php echo $producto['stock']; ?>" required><br>

    <button type="submit" name="actualizar_producto">Actualizar Producto</button>
</form>
