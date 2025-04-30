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

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
    header('Content-Type: application/json');

    if (isset($_GET['id']) && isset($_GET['cantidad'])) {
        $producto_id = intval($_GET['id']);
        $cantidad = intval($_GET['cantidad']);

        if ($producto_id <= 0 || $cantidad <= 0) {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
            exit();
        }

        $stmt = $conn->prepare("SELECT * FROM producto WHERE id = ?");
        $stmt->bind_param("i", $producto_id);
        $stmt->execute();
        $producto = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($producto) {
            if ($cantidad > $producto['stock']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'La cantidad solicitada excede el stock disponible. Stock actual: ' . $producto['stock']
                ]);
                exit();
            }

            $encontrado = false;
            foreach ($_SESSION['carrito'] as &$item) {
                if ($item['id'] == $producto_id) {
                    $item['cantidad'] += $cantidad;
                    if ($item['cantidad'] > $producto['stock']) {
                        $item['cantidad'] = $producto['stock'];
                    }
                    $item['subtotal'] = $item['cantidad'] * $producto['precio'];
                    $encontrado = true;
                    break;
                }
            }
            if (!$encontrado) {
                $_SESSION['carrito'][] = [
                    'id' => $producto['id'],
                    'nombre' => $producto['nombre'],
                    'precio' => $producto['precio'],
                    'imagen' => $producto['imagen'],
                    'cantidad' => $cantidad,
                    'subtotal' => $producto['precio'] * $cantidad,
                    'stock' => $producto['stock']
                ];
            }

            $total = array_sum(array_column($_SESSION['carrito'], 'subtotal'));

            echo json_encode([
                'success' => true,
                'message' => 'Producto agregado al carrito.',
                'total' => $total,
                'carrito' => $_SESSION['carrito']
            ]);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado.']);
            exit();
        }
    }

    if (isset($_GET['eliminar'])) {
        $producto_id = intval($_GET['eliminar']);

        if ($producto_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
            exit();
        }

        foreach ($_SESSION['carrito'] as $key => $item) {
            if ($item['id'] == $producto_id) {
                unset($_SESSION['carrito'][$key]);
                break;
            }
        }

        $total = array_sum(array_column($_SESSION['carrito'], 'subtotal'));

        echo json_encode([
            'success' => true,
            'message' => 'Producto eliminado del carrito.',
            'total' => $total,
            'carrito' => $_SESSION['carrito']
        ]);
        exit();
    }

    if (isset($_GET['actualizar']) && isset($_GET['cantidad'])) {
        $producto_id = intval($_GET['actualizar']);
        $cantidad = intval($_GET['cantidad']);

        if ($cantidad < 1) {
            echo json_encode(['success' => false, 'message' => 'La cantidad no puede ser menor a 1.']);
            exit();
        }

        foreach ($_SESSION['carrito'] as &$item) {
            if ($item['id'] == $producto_id) {
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

    echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
    exit();
}

$total = array_sum(array_column($_SESSION['carrito'], 'subtotal'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (empty($_SESSION['carrito'])) {
        echo json_encode(['success' => false, 'message' => 'El carrito está vacío.']);
        exit();
    }

    $usuario_id = $_SESSION['usuario_id'];
    $total = array_sum(array_column($_SESSION['carrito'], 'subtotal'));

    // Insertar pedido
    $stmt = $conn->prepare("INSERT INTO pedido (usuario_id, total, estado) VALUES (?, ?, 'pendiente')");
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
    $stmtDetalle = $conn->prepare("INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, subtotal) VALUES (?, ?, ?, ?)");
    if (!$stmtDetalle) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar los productos del pedido: ' . $conn->error]);
        exit();
    }

    foreach ($_SESSION['carrito'] as $item) {
        $idProducto = $item['id'];
        $cantidad = $item['cantidad'];
        $precio = $item['precio'];
        $subtotal = $cantidad * $precio;

        $stmtDetalle->bind_param("iiid", $pedido_id, $idProducto, $cantidad, $subtotal);
        if (!$stmtDetalle->execute()) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al insertar producto del pedido: ' . $stmtDetalle->error
            ]);
            exit();
        }
    }
    $stmtDetalle->close();

    $_SESSION['carrito'] = [];

    echo json_encode(['success' => true, 'message' => 'Pedido realizado con éxito.']);
    exit();
}

echo $twig->render('compra.twig', [
    'carrito' => $_SESSION['carrito'],
    'total' => $total,
    'css_url' => '../public/assets/css/style-consumidor.css',
    'session' => $_SESSION
]);
?>
