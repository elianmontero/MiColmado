<?php
include 'config.php';
session_start();
require_once '../vendor/autoload.php';

// Cargar Twig
$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);
$twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
    return '/assets/' . ltrim($path, '/');
}));

// Asegurar autenticación
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

// Inicializar carrito
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

try {
    // Calcular total actual
    $total = array_sum(array_column($_SESSION['carrito'], 'subtotal'));
    $total = floatval($total);

    // Función auxiliar para respuestas JSON AJAX
    function responder($array) {
        header('Content-Type: application/json');
        echo json_encode($array);
        exit();
    }

    // Detectar petición AJAX
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAjax) {
        $accion = $_POST['accion'] ?? '';

        // FINALIZAR COMPRA
        if ($accion === 'finalizar') {
            if (empty($_SESSION['carrito'])) {
                responder(['success' => false, 'message' => 'El carrito está vacío.']);
            }

            $usuario_id  = $_SESSION['usuario_id'];
            $metodo_pago = $_POST['metodo_pago'] ?? 'efectivo';
            $direccion   = trim($_POST['direccion'] ?? '');
            $metodo_pago = in_array($metodo_pago, ['efectivo', 'tarjeta']) ? $metodo_pago : 'efectivo';

            if ($direccion === '') {
                responder(['success' => false, 'message' => 'La dirección es obligatoria.']);
            }

            $total = floatval(array_sum(array_column($_SESSION['carrito'], 'subtotal')));

            // Insertar pedido
            $stmt = $conn->prepare(
                "INSERT INTO pedido (id_usuario, total, estado, metodo_pago, direccion)
                 VALUES (?, ?, 'pendiente', ?, ?)"
            );
            if (!$stmt) {
                responder(['success' => false, 'message' => 'Ocurrió un error al procesar el pedido.']);
            }
            $stmt->bind_param("idss", $usuario_id, $total, $metodo_pago, $direccion);
            if (!$stmt->execute()) {
                responder(['success' => false, 'message' => 'No se pudo registrar el pedido.']);
            }
            $pedido_id = $stmt->insert_id;
            $stmt->close();

            // Insertar detalle de pedido
            $stmtDet = $conn->prepare(
                "INSERT INTO pedido_producto (id_pedido, id_producto, cantidad, subtotal) VALUES (?, ?, ?, ?)"
            );
            if (!$stmtDet) {
                responder(['success' => false, 'message' => 'Error al procesar los productos.']);
            }

            foreach ($_SESSION['carrito'] as $item) {
                $stmtDet->bind_param("iiid", $pedido_id, $item['id'], $item['cantidad'], $item['subtotal']);
                if (!$stmtDet->execute()) {
                    responder(['success' => false, 'message' => 'No se pudo agregar un producto al pedido.']);
                }

                // Actualizar stock
                $stmtStock = $conn->prepare("UPDATE producto SET stock = stock - ? WHERE id = ?");
                if (!$stmtStock) {
                    responder(['success' => false, 'message' => 'Error al actualizar el inventario.']);
                }
                $stmtStock->bind_param("ii", $item['cantidad'], $item['id']);
                $stmtStock->execute();
                $stmtStock->close();
            }
            $stmtDet->close();

            $_SESSION['carrito'] = [];

            responder(['success' => true, 'message' => 'Pedido realizado con éxito.']);
        }

        // ELIMINAR PRODUCTO
        if ($accion === 'eliminar') {
            $idProducto = intval($_POST['id_producto']);
            foreach ($_SESSION['carrito'] as $i => $prod) {
                if ($prod['id'] === $idProducto) {
                    unset($_SESSION['carrito'][$i]);
                    $_SESSION['carrito'] = array_values($_SESSION['carrito']);
                    responder(['success' => true, 'message' => 'Producto eliminado del carrito.']);
                }
            }
            responder(['success' => false, 'message' => 'Producto no encontrado en el carrito.']);
        }

        // ACTUALIZAR CANTIDAD
        if ($accion === 'actualizar') {
            $idProducto = intval($_POST['id_producto']);
            $cantidad   = intval($_POST['cantidad']);
            if ($cantidad < 1) {
                responder(['success' => false, 'message' => 'La cantidad no puede ser menor a 1.']);
            }
            foreach ($_SESSION['carrito'] as &$prod) {
                if ($prod['id'] === $idProducto) {
                    if ($cantidad > $prod['stock']) {
                        responder(['success' => false, 'message' => 'Supera el stock disponible.']);
                    }
                    $prod['cantidad'] = $cantidad;
                    $prod['subtotal'] = $cantidad * $prod['precio'];
                    break;
                }
            }
            $nuevoTotal = array_sum(array_column($_SESSION['carrito'], 'subtotal'));
            responder([
                'success'  => true,
                'subtotal' => number_format($prod['subtotal'], 2),
                'total'    => number_format($nuevoTotal, 2)
            ]);
        }
    }

    // Renderizar página (no AJAX)
    echo $twig->render('compra.twig', [
        'carrito'       => $_SESSION['carrito'],
        'total'         => $total,
        'css_url'       => '../public/assets/css/style-consumidor.css',
        'session'       => $_SESSION,
        'mensaje_error' => $_SESSION['mensaje_error'] ?? null,
        'mensaje_exito' => $_SESSION['mensaje_exito'] ?? null,
    ]);

    unset($_SESSION['mensaje_error'], $_SESSION['mensaje_exito']);

} catch (Exception $e) {
    if (!empty($isAjax)) {
        responder(['success' => false, 'message' => 'Ocurrió un error inesperado.']);
    }
    echo "<p>Ocurrió un error inesperado. Por favor, intenta nuevamente más tarde.</p>";
}
?>
