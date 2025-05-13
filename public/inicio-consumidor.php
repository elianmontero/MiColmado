<?php
include 'config.php';

// Permitir varias sesiones activas por usuario/pestaña
if (isset($_GET['session_name'])) {
    session_name($_GET['session_name']);
} elseif (isset($_POST['session_name'])) {
    session_name($_POST['session_name']);
} elseif (isset($_COOKIE['session_name'])) {
    session_name($_COOKIE['session_name']);
}
session_start();

require_once '../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

$twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
    return '/assets/' . ltrim($path, '/');
}));

$twig->addFunction(new \Twig\TwigFunction('session_url', function ($url) {
    $sessionName = session_name();
    $sessionId = session_id();
    if (!$sessionName || !$sessionId) return $url;
    $sep = (strpos($url, '?') !== false) ? '&' : '?';
    return $url . $sep . 'session_name=' . $sessionName;
}));

// Verificar si es una solicitud para agregar un producto al carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    header('Content-Type: application/json');

    $idProducto = intval($_POST['id_producto']);
    $cantidad = intval($_POST['cantidad']);

    // Validar parámetros
    if ($idProducto <= 0 || $cantidad <= 0) {
        echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
        exit();
    }

    // Obtener información del producto desde la base de datos
    $stmt = $conn->prepare("SELECT id, nombre, precio, stock, imagen FROM producto WHERE id = ?");
    $stmt->bind_param("i", $idProducto);
    $stmt->execute();
    $producto = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$producto) {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado.']);
        exit();
    }

    // Verificar si hay suficiente stock
    if ($cantidad > $producto['stock']) {
        echo json_encode(['success' => false, 'message' => 'La cantidad solicitada excede el stock disponible.']);
        exit();
    }

    // Agregar o actualizar el producto en el carrito
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    $encontrado = false;
    foreach ($_SESSION['carrito'] as &$item) {
        if ($item['id'] == $idProducto) {
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
            'imagen' => $producto['imagen'], // Asegúrate de incluir la imagen
            'cantidad' => $cantidad,
            'subtotal' => $producto['precio'] * $cantidad,
            'stock' => $producto['stock']
        ];
    }

    // Calcular el total del carrito
    $total = array_sum(array_column($_SESSION['carrito'], 'subtotal'));

    echo json_encode([
        'success' => true,
        'message' => 'Producto agregado al carrito.',
        'carrito' => $_SESSION['carrito'],
        'total' => $total
    ]);
    exit();
}

// Obtener lista de productos
$productos = [];
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Si hay búsqueda, traer todos los productos que coincidan
if ($search) {
    $stmt = $conn->prepare("SELECT * FROM producto WHERE nombre LIKE ?");
    $search_param = "%" . $search . "%";
    $stmt->bind_param("s", $search_param);
} else {
    // Si no hay búsqueda, solo traer 5 productos aleatorios
    $stmt = $conn->prepare("SELECT * FROM producto ORDER BY RAND() LIMIT 5");
}

$stmt->execute();
$resultado = $stmt->get_result();
while ($fila = $resultado->fetch_assoc()) {
    $productos[] = $fila;
}
$stmt->close();

// Verificar si es una solicitud AJAX
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
    // Configurar encabezado JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'productos' => $productos
    ]);
    exit();
}

// Renderizar la plantilla Twig para solicitudes normales
echo $twig->render('inicio-consumidor.twig', [
    'productos' => $productos,
    'css_url' => '../public/assets/css/style-consumidor.css',
    'session' => $_SESSION,
    'logout_url' => 'logout.php',
    'session_name' => session_name()
]);
?>
