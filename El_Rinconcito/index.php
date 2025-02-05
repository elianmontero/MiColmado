<?php
include 'config.php';
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Virtual</title>
</head>
<body>
    <h2>Bienvenido a la Tienda</h2>

    <?php if (isset($_SESSION['usuario_nombre'])): ?>
        <p>Hola, <?php echo $_SESSION['usuario_nombre']; ?> | <a href="logout.php">Cerrar sesión</a></p>
    <?php else: ?>
        <p><a href="login.php">Iniciar Sesión</a> | <a href="registro.php">Registrarse</a></p>
    <?php endif; ?>

    <h3>Lista de Productos</h3>
    <div>
        <?php
        $result = $conn->query("SELECT * FROM productos WHERE stock > 0");
        while ($row = $result->fetch_assoc()):
        ?>
            <div style="border:1px solid #000; padding:10px; margin:10px;">
                <h4><?php echo $row['nombre']; ?></h4>
                <p><?php echo $row['descripcion']; ?></p>
                <p>Precio: $<?php echo $row['precio']; ?></p>
                <p>Stock disponible: <?php echo $row['stock']; ?></p>
            </div>
        <?php endwhile; ?>
    </div>

</body>
</html>
