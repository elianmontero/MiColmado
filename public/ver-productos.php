<?php
include '../public/config.php';
session_start();
require_once '../vendor/autoload.php';

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

echo $twig->render('ver-productos.twig', [
    'productos' => $productos,
    'css_url' => '../public/assets/css/style-proveedor.css',
    'session' => $_SESSION
]);
?>