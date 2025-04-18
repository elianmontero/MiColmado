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

// Pasar la información de la sesión y los productos a la plantilla
echo $twig->render('home.twig', [
    'css_url' => '../public/assets/css/style.css',
    'session' => $_SESSION,
    'productos' => $productos
]);
?>
