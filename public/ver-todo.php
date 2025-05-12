<?php
include 'config.php';
session_start();

require_once '../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);
$twig->addFunction(new \Twig\TwigFunction('asset', fn($p) => '/assets/' . ltrim($p, '/')));

// Si viene un POST para agregar al carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'agregar') {
    header('Content-Type: application/json; charset=utf-8');

    $idProducto = intval($_POST['id_producto'] ?? 0);
    $cantidad   = intval($_POST['cantidad']   ?? 0);

    if ($idProducto <= 0 || $cantidad <= 0) {
        echo json_encode(['success'=>false,'message'=>'Parámetros inválidos.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id,nombre,precio,stock,imagen FROM producto WHERE id=?");
    $stmt->bind_param("i",$idProducto);
    $stmt->execute();
    $producto = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$producto) {
        echo json_encode(['success'=>false,'message'=>'Producto no encontrado.']);
        exit;
    }
    if ($cantidad > $producto['stock']) {
        echo json_encode(['success'=>false,'message'=>'No hay tanto stock.']);
        exit;
    }

    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    $found = false;
    foreach ($_SESSION['carrito'] as &$item) {
        if ($item['id']==$idProducto) {
            $item['cantidad'] += $cantidad;
            if ($item['cantidad'] > $producto['stock']) {
                $item['cantidad'] = $producto['stock'];
            }
            $item['subtotal'] = $item['cantidad'] * $producto['precio'];
            $found = true;
            break;
        }
    }
    if (!$found) {
        $_SESSION['carrito'][] = [
            'id'       => $producto['id'],
            'nombre'   => $producto['nombre'],
            'precio'   => $producto['precio'],
            'imagen'   => $producto['imagen'],
            'cantidad' => $cantidad,
            'subtotal' => $producto['precio'] * $cantidad,
            'stock'    => $producto['stock'],
        ];
    }

    $total = array_sum(array_column($_SESSION['carrito'],'subtotal'));
    echo json_encode(['success'=>true,'message'=>'¡Agregado al carrito!','carrito'=>$_SESSION['carrito'],'total'=>$total]);
    exit;
}

// Cargar productos según búsqueda
$productos = [];
$search    = $_GET['search'] ?? '';
if ($search !== '') {
    $stmt = $conn->prepare("SELECT * FROM producto WHERE nombre LIKE ?");
    $like = '%' . $search . '%';
    $stmt->bind_param("s", $like);
} else {
    $stmt = $conn->prepare("SELECT * FROM producto");
}
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $productos[] = $row;
}
$stmt->close();

// Render Twig
echo $twig->render('ver-todo.twig', [
    'productos' => $productos,
    'css_url'   => '/public/assets/css/style-consumidor.css',
    'session'   => $_SESSION,
    'search'    => $search,
]);
?>