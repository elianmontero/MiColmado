<?php
include '../public/config.php';
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

// Inicializar variables
$mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : null;
$productos = [];

// Obtener lista de productos asociados al colmado del usuario
$usuario_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT id FROM colmado WHERE id_usuario = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$colmado = $resultado->fetch_assoc();

if (!$colmado) {
    $mensaje = 'No tienes un colmado asociado. Regístrate como proveedor para agregar productos.';
} else {
    $id_colmado = $colmado['id'];

    $stmt = $conn->prepare("SELECT * FROM producto WHERE id_colmado = ? ORDER BY id ASC");
    $stmt->bind_param("i", $id_colmado);
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $productos[] = $fila;
    }
}

// Renderizar la plantilla
echo $twig->render('ver-productos.twig', [
    'productos' => $productos,
    'mensaje' => $mensaje,
    'css_url' => '../public/assets/css/style-proveedor.css',
    'session' => $_SESSION
]);
?>