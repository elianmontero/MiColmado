<?php
include 'config.php';
session_start();
require_once '../vendor/autoload.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

$twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
    return '/assets/' . ltrim($path, '/');
}));

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$total = array_sum(array_column($_SESSION['carrito'], 'subtotal'));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] === 'finalizar') {
    if (empty($_SESSION['carrito'])) {
        $_SESSION['mensaje_error'] = 'El carrito está vacío.';
        header("Location: compra.php");
        exit();
    }

    $usuario_id = $_SESSION['usuario_id'];
    $total = array_sum(array_column($_SESSION['carrito'], 'subtotal'));

    // Insertar pedido
    $stmt = $conn->prepare("INSERT INTO pedido (id_usuario, total, estado) VALUES (?, ?, 'pendiente')");
    if (!$stmt) {
        $_SESSION['mensaje_error'] = 'Error al preparar el pedido: ' . $conn->error;
        header("Location: compra.php");
        exit();
    }

    $stmt->bind_param("id", $usuario_id, $total);
    if (!$stmt->execute()) {
        $_SESSION['mensaje_error'] = 'Error al insertar el pedido: ' . $stmt->error;
        header("Location: compra.php");
        exit();
    }

    $pedido_id = $stmt->insert_id;
    $stmt->close();

    // Insertar detalle del pedido
    $stmtDetalle = $conn->prepare("INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, subtotal) VALUES (?, ?, ?, ?)");
    if (!$stmtDetalle) {
        $_SESSION['mensaje_error'] = 'Error al preparar los productos del pedido: ' . $conn->error;
        header("Location: compra.php");
        exit();
    }

    foreach ($_SESSION['carrito'] as $item) {
    $idProducto = $item['id'];
        $cantidad = $item['cantidad'];
        $precio = $item['precio'];
        $subtotal = $cantidad * $precio;

        $stmtDetalle->bind_param("iiid", $pedido_id, $idProducto, $cantidad, $subtotal);
        if (!$stmtDetalle->execute()) {
            $_SESSION['mensaje_error'] = 'Error al insertar producto del pedido: ' . $stmtDetalle->error;
            header("Location: compra.php");
            exit();
        }
    }
    $stmtDetalle->close();

    // Limpiar el carrito después de confirmar el pedido
    $_SESSION['carrito'] = [];
    $_SESSION['mensaje_exito'] = 'Pedido realizado con éxito.';
    header("Location: compra.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] === 'eliminar') {
    $idProducto = $_POST['id_producto'];

    // Verificar si el producto existe en el carrito
    foreach ($_SESSION['carrito'] as $index => $item) {
        if ($item['id'] == $idProducto) {
            unset($_SESSION['carrito'][$index]); // Eliminar el producto del carrito
            $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexar el array
            $_SESSION['mensaje_exito'] = 'Producto eliminado del carrito.';
            break;
        }
    }

    // Si no está en el carrito, agregarlo
    if (!$productoEncontrado) {
        // Obtener información del producto desde la base de datos
        $stmt = $conn->prepare("SELECT id, nombre, precio FROM producto WHERE id = ?");
        $stmt->bind_param("i", $idProducto);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $producto = $resultado->fetch_assoc();

        if ($producto) {
            $_SESSION['carrito'][] = [
                'id' => $producto['id'],
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio'],
                'cantidad' => $cantidad,
                'subtotal' => $producto['precio'] * $cantidad
            ];
        }
        $stmt->close();
    }

    // Responder con JSON para solicitudes AJAX
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Producto agregado al carrito.']);
    exit();
}

// Renderizar la plantilla Twig
echo $twig->render('compra.twig', [
    'carrito' => $_SESSION['carrito'],
    'total' => $total,
    'css_url' => '../public/assets/css/style-consumidor.css',
    'session' => $_SESSION,
    'mensaje_error' => $_SESSION['mensaje_error'] ?? null,
    'mensaje_exito' => $_SESSION['mensaje_exito'] ?? null
]);

// Limpiar mensajes de sesión después de mostrarlos
unset($_SESSION['mensaje_error'], $_SESSION['mensaje_exito']);
?>
