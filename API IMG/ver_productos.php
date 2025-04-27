<?php
include 'php/conectar.php';

$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

if (!empty($busqueda)) {
    // Dividir la búsqueda en términos individuales
    $terminos = explode(' ', $busqueda);
    $conditions = [];
    $params = [];
    $types = '';

    foreach ($terminos as $termino) {
        if (!empty($termino)) {
            $conditions[] = "(nombre LIKE ? OR marca LIKE ? OR categorias LIKE ?)";
            $searchTerm = "%$termino%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'sss';
        }
    }

    $where = !empty($conditions) ? implode(' AND ', $conditions) . ' AND pais = "República Dominicana"' : 'pais = "República Dominicana"';
    $sql = "SELECT * FROM productos WHERE $where ORDER BY nombre ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM productos WHERE pais = 'República Dominicana' ORDER BY nombre ASC";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos Dominicanos</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>Productos Dominicanos</h1>
        <nav>
            <a href="index.html">Inicio</a>
            <a href="agregar.html">Agregar Más</a>
        </nav>
    </header>
    <main>
        <div class="encabezado">
            <h2>Catálogo de Productos</h2>
            <p>Mostrando productos de República Dominicana</p>
            
            <div class="search-container">
                <form action="ver_productos.php" method="get">
                    <input type="text" name="busqueda" class="search-box" 
                           placeholder="Buscar productos dominicanos..." 
                           value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button type="submit" class="search-button">Buscar</button>
                    <?php if (!empty($busqueda)): ?>
                        <a href="ver_productos.php" class="clear-search">Limpiar búsqueda</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <div class="producto-container">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '
                    <div class="producto">
                        <h3>' . htmlspecialchars($row['nombre']) . '</h3>
                        <p class="marca">' . htmlspecialchars($row['marca']) . '</p>';
                    
                    if (!empty($row['imagen_url'])) {
                        echo '<img src="' . htmlspecialchars($row['imagen_url']) . '" alt="' . htmlspecialchars($row['nombre']) . '">';
                    } else {
                        echo '<div class="sin-imagen">Imagen no disponible</div>';
                    }
                    
                    echo '
                        <div class="precio">' . 
                            ($row['precio'] != 'N/A' ? 'RD$ ' . htmlspecialchars($row['precio']) : 'Precio no disponible') . 
                        '</div>
                        <button onclick="window.location.href=\'editar_producto.php?id=' . $row['id'] . '\'">Editar Producto</button>
                    </div>
                    ';
                }
            } else {
                echo '<div class="sin-productos">' . 
                     (empty($busqueda) ? 'No hay productos guardados aún.' : 'No se encontraron productos que coincidan con "' . htmlspecialchars($busqueda) . '"') . 
                     '</div>';
            }
            ?>
        </div>
    </main>
</body>
</html>
