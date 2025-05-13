<?php
include '../public/config.php';

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

$twig->addFunction(new \Twig\TwigFunction('session_url', function ($url) {
    $sessionName = session_name();
    $sessionId = session_id();
    if (!$sessionName || !$sessionId) return $url;
    $sep = (strpos($url, '?') !== false) ? '&' : '?';
    return $url . $sep . 'session_name=' . $sessionName;
}));

// Inicializar variables
$mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : null;
$productos = [];

// Obtener lista de productos asociados al colmado del usuario (según usuario en sesión)
$usuario_id = $_SESSION['usuario_id'];

// Buscar todos los productos cuyo id_colmado pertenezca a un colmado de este usuario
$stmt = $conn->prepare(
    "SELECT p.* 
     FROM producto p
     INNER JOIN colmado c ON p.id_colmado = c.id
     WHERE c.id_usuario = ?
     ORDER BY p.id ASC"
);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
while ($fila = $resultado->fetch_assoc()) {
    $productos[] = $fila;
}
$stmt->close();

// Renderizar la plantilla
echo $twig->render('ver-productos.twig', [
    'productos' => $productos,
    'mensaje' => $mensaje,
    'css_url' => '../public/assets/css/style-proveedor.css',
    'session' => $_SESSION,
    'session_name' => session_name()
]);
?>