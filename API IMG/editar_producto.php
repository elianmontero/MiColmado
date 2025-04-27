<?php
include 'php/conectar.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;

if ($id) {
    $sql = "SELECT * FROM productos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $producto = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $marca = $_POST['marca'];
    $precio = $_POST['precio'];
    $imagen_url = $_POST['imagen_url'];
    
    $sql = "UPDATE productos SET nombre = ?, marca = ?, precio = ?, imagen_url = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssi', $nombre, $marca, $precio, $imagen_url, $id);
    $stmt->execute();

    echo '<p>Producto actualizado correctamente!</p>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
</head>
<body>
    <header>
        <h1>Editar Producto</h1>
    </header>
    <main>
        <form method="post">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
            
            <label for="marca">Marca:</label>
            <input type="text" id="marca" name="marca" value="<?php echo htmlspecialchars($producto['marca']); ?>" required>
            
            <label for="precio">Precio:</label>
            <input type="text" id="precio" name="precio" value="<?php echo htmlspecialchars($producto['precio']); ?>" required>
            
            <label for="imagen_url">Imagen URL:</label>
            <input type="text" id="imagen_url" name="imagen_url" value="<?php echo htmlspecialchars($producto['imagen_url']); ?>">
            
            <button type="submit">Guardar Cambios</button>
        </form>
        <a href="ver_productos.php">Volver atras...</a>
    </main>
</body>
</html>
