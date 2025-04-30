<?php
include 'config.php';
session_start();
require_once '../vendor/autoload.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

$twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
    return '/assets/' . ltrim($path, '/');
}));

// Obtener el carrito de la sesión
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Verificar si la solicitud es AJAX
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
    header('Content-Type: application/json');

    // Lógica AJAX: Agregar producto con cantidad
    if (isset($_GET['id']) && isset($_GET['cantidad'])) {
        $producto_id = intval($_GET['id']);
        $cantidad = intval($_GET['cantidad']);

        // Validar parámetros
        if ($producto_id <= 0 || $cantidad <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Parámetros inválidos.'
            ]);
            exit();
        }

        // Obtener el producto de la base de datos
        $stmt = $conn->prepare("SELECT * FROM producto WHERE id = ?");
        $stmt->bind_param("i", $producto_id);
        $stmt->execute();
        $producto = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($producto) {
            // Validar que la cantidad solicitada no exceda el stock
            if ($cantidad > $producto['stock']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'La cantidad solicitada excede el stock disponible. Stock actual: ' . $producto['stock']
                ]);
                exit();
            }

            // Agregar o actualizar el producto en el carrito
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

            // Calcular el total
            $total = array_sum(array_column($_SESSION['carrito'], 'subtotal'));

            // Responder con JSON
            echo json_encode([
                'success' => true,
                'message' => 'Producto agregado al carrito.',
                'total' => $total,
                'carrito' => $_SESSION['carrito']
            ]);
            exit();
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Producto no encontrado.'
            ]);
            exit();
        }
    }

    // Lógica AJAX: Eliminar producto del carrito
    if (isset($_GET['eliminar'])) {
        $producto_id = intval($_GET['eliminar']);

        // Validar parámetros
        if ($producto_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Parámetros inválidos.'
            ]);
            exit();
        }

        // Eliminar el producto del carrito
        foreach ($_SESSION['carrito'] as $key => $item) {
            if ($item['id'] == $producto_id) {
                unset($_SESSION['carrito'][$key]);
                break;
            }
        }

        // Recalcular el total
        $total = array_sum(array_column($_SESSION['carrito'], 'subtotal'));

        // Responder con JSON
        echo json_encode([
            'success' => true,
            'message' => 'Producto eliminado del carrito.',
            'total' => $total,
            'carrito' => $_SESSION['carrito']
        ]);
        exit();
    }

    // Si no entra en ninguna de las anteriores
    echo json_encode([
        'success' => false,
        'message' => 'Solicitud inválida.'
    ]);
    exit();
}

// Si no es AJAX, procesamos la lógica normal y mostramos la página
$total = array_sum(array_column($_SESSION['carrito'], 'subtotal'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];

    // Insertar pedido
    $stmt = $conn->prepare("INSERT INTO pedido (usuario_id, total) VALUES (?, ?)");
    $stmt->bind_param("id", $usuario_id, $total);
    $stmt->execute();
    $pedido_id = $stmt->insert_id;
    $stmt->close();

    // Insertar productos del pedido
    $stmt = $conn->prepare("INSERT INTO pedido_producto (pedido_id, producto_id, cantidad, subtotal) VALUES (?, ?, ?, ?)");
    foreach ($_SESSION['carrito'] as $item) {
        $stmt->bind_param("iiid", $pedido_id, $item['id'], $item['cantidad'], $item['subtotal']);
        $stmt->execute();
    }
    $stmt->close();

    // Vaciar el carrito
    $_SESSION['carrito'] = [];

    // Redirigir a una página de confirmación
    header("Location: confirmacion.php");
    exit();
}

// Renderizar la página de compra
echo $twig->render('compra.twig', [
    'carrito' => $_SESSION['carrito'],
    'total' => $total,
    'css_url' => '../public/assets/css/style-consumidor.css',
    'session' => $_SESSION
]);
?>