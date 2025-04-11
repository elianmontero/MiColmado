<?php
include 'config.php';
session_start();
require_once '../vendor/autoload.php';

// Verificar si hay una sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

$twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
    return '/assets/' . ltrim($path, '/');
}));

// Obtener lista de productos
$sql = "SELECT * FROM PRODUCTO";
$resultado = $conn->query($sql);
$productos = [];
while ($fila = $resultado->fetch_assoc()) {
    $productos[] = $fila;
}

echo $twig->render('inicio-proveedor.twig', [
    'productos' => $productos,
    'css_url' => '../public/assets/css/style-proveedor.css',
    'session' => $_SESSION,
    'mensaje' => isset($mensaje) ? $mensaje : '',
    'logout_url' => 'logout.php'
]);
?>
