<?php
include 'config.php';
session_start();

require_once '../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

$twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
    return '/assets/' . ltrim($path, '/');
}));

// Obtener lista de productos
$productos = [];
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $stmt = $conn->prepare("SELECT * FROM PRODUCTO WHERE nombre LIKE ?");
    $search_param = "%" . $search . "%";
    $stmt->bind_param("s", $search_param);
} else {
    $stmt = $conn->prepare("SELECT * FROM PRODUCTO");
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
    'logout_url' => 'logout.php'
]);
?>
