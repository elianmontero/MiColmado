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
    header('Content-Type: application/json');

    if (empty($_SESSION['carrito'])) {
        echo json_encode(['success' => false, 'message' => 'El carrito está vacío.']);
        exit();
    }

    $usuario_id = $_SESSION['usuario_id'];
    $total = array_sum(array_column($_SESSION['carrito'], 'subtotal'));

    // Insertar pedido
    $stmt = $conn->prepare("INSERT INTO pedido (id_usuario, total, estado) VALUES (?, ?, 'pendiente')");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar el pedido: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param("id", $usuario_id, $total);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error al insertar el pedido: ' . $stmt->error]);
        exit();
    }

    $pedido_id = $stmt->insert_id;
    $stmt->close();

    // Insertar detalle del pedido
    $stmtDetalle = $conn->prepare("INSERT INTO pedido_producto (id_pedido, id_producto, cantidad, subtotal) VALUES (?, ?, ?, ?)");
    if (!$stmtDetalle) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar los productos del pedido: ' . $conn->error]);
        exit();
    }

    foreach ($_SESSION['carrito'] as $item) {
        $idProducto = $item['id'];
        $cantidad = $item['cantidad'];
        $subtotal = $item['subtotal'];

        $stmtDetalle->bind_param("iiid", $pedido_id, $idProducto, $cantidad, $subtotal);
        if (!$stmtDetalle->execute()) {
            echo json_encode(['success' => false, 'message' => 'Error al insertar producto del pedido: ' . $stmtDetalle->error]);
            exit();
        }

        // Descontar la cantidad comprada del stock
        $stmtStock = $conn->prepare("UPDATE producto SET stock = stock - ? WHERE id = ?");
        if (!$stmtStock) {
            echo json_encode(['success' => false, 'message' => 'Error al preparar la actualización del stock: ' . $conn->error]);
            exit();
        }

        $stmtStock->bind_param("ii", $cantidad, $idProducto);
        if (!$stmtStock->execute()) {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el stock del producto: ' . $stmtStock->error]);
            exit();
        }
        $stmtStock->close();
    }
    $stmtDetalle->close();

    // Limpiar el carrito después de confirmar el pedido
    $_SESSION['carrito'] = [];

    echo json_encode(['success' => true, 'message' => 'Pedido realizado con éxito.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] === 'eliminar') {
    header('Content-Type: application/json');

    $idProducto = intval($_POST['id_producto']);

    // Verificar si el producto existe en el carrito
    foreach ($_SESSION['carrito'] as $index => $item) {
        if ($item['id'] == $idProducto) {
            unset($_SESSION['carrito'][$index]); // Eliminar el producto del carrito
            $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexar el array
            echo json_encode(['success' => true, 'message' => 'Producto eliminado del carrito.']);
            exit();
        }
    }

    echo json_encode(['success' => false, 'message' => 'Producto no encontrado en el carrito.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] === 'actualizar') {
    header('Content-Type: application/json');

    $idProducto = intval($_POST['id_producto']);
    $cantidad = intval($_POST['cantidad']);

    if ($cantidad < 1) {
        echo json_encode(['success' => false, 'message' => 'La cantidad no puede ser menor a 1.']);
        exit();
    }

    foreach ($_SESSION['carrito'] as &$item) {
        if ($item['id'] == $idProducto) {
            if ($cantidad > $item['stock']) {
                echo json_encode(['success' => false, 'message' => 'La cantidad no puede ser mayor al stock disponible.']);
                exit();
            }
            $item['cantidad'] = $cantidad;
            $item['subtotal'] = $cantidad * $item['precio'];
            break;
        }
    }

    $total = array_sum(array_column($_SESSION['carrito'], 'subtotal'));

    echo json_encode([
        'success' => true,
        'subtotal' => number_format($item['subtotal'], 2),
        'total' => number_format($total, 2)
    ]);
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
