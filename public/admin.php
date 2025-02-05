<?php
include 'config.php';
session_start();

// Asegurarse que el usuario está autenticado
if (!isset($_SESSION['usuario_rol'])) {
    die("Acceso denegado. No ha iniciado sesión. <a href='login.php'>Iniciar sesión</a>");
}

// Asegurarse que el usuario tenga el rol 'admin'
if ($_SESSION['usuario_rol'] !== 'admin') {
    die("Acceso denegado. Solo administradores pueden acceder. <a href='index.php'>Volver</a>");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
</head>
<body>
    <h2>Panel de Administración</h2>
    <p>Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?> | <a href="logout.php">Cerrar sesión</a></p>
    <p><a href="index.php">Volver a la tienda</a></p>

    <h3>Agregar Producto</h3>
<form action="admin.php" method="POST">
    <label>Nombre:</label>
    <input type="text" name="nombre" required><br>

    <label>Descripción:</label>
    <textarea name="descripcion" required></textarea><br>

    <label>Precio:</label>
    <input type="number" step="0.01" name="precio" required><br>

    <label>Stock:</label>
    <input type="number" name="stock" required><br>

    <label>Imagen:</label>
    <input type="file" name="imagen"><br>

    <button type="submit" name="agregar_producto">Agregar Producto</button>
</form>

<?php
if (isset($_POST['agregar_producto'])) {
    // Obtener los datos del formulario
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];

    // Manejo de la imagen (si se sube una)
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        // Obtener la extensión del archivo
        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);

        // Definir el nombre del archivo en la carpeta uploads
        $imagen = 'uploads/' . uniqid() . '.' . $ext;

        // Mover el archivo a la carpeta uploads
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $imagen)) {
            // Imagen subida con éxito
            echo "<p>Imagen subida correctamente.</p>";
        } else {
            echo "<p>Error al subir la imagen.</p>";
            $imagen = null;  // Si hay error, no almacenamos imagen
        }
    } else {
        $imagen = null;  // Si no se subió imagen
    }

    // Preparar la consulta para insertar el producto
    $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, imagen) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdis", $nombre, $descripcion, $precio, $stock, $imagen);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo "<p>Producto agregado con éxito.</p>";
    } else {
        echo "<p>Error al agregar el producto.</p>";
    }
    $stmt->close();
}
?>

<h3>Inventario de Productos</h3>
<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Imagen</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Obtener todos los productos
        $result = $conn->query("SELECT * FROM productos");

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['nombre'] . "</td>";
            echo "<td>" . $row['descripcion'] . "</td>";
            echo "<td>" . $row['precio'] . "</td>";
            echo "<td>" . $row['stock'] . "</td>";

            // Verificar la ruta de la imagen
            echo "<td>";
            if ($row['imagen']) {
                echo "Ruta de imagen: " . $row['imagen']; // Mostrar la ruta de la imagen
                echo "<br><img src='" . $row['imagen'] . "' width='100'>";
            } else {
                echo "No disponible";
            }
            echo "</td>";

            echo "<td>
                <a href='editar_producto.php?id=" . $row['id'] . "'>Editar</a> |
                <a href='eliminar_producto.php?id=" . $row['id'] . "'>Eliminar</a>
            </td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

</body>
</html>

