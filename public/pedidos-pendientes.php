<?php
include '../public/config.php';
session_start();
require_once '../vendor/autoload.php';


$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

// Obtener pedidos pendientes
$stmt = $conn->prepare("SELECT p.id, p.total, p.estado, u.nombre_completo AS usuario_nombre 
                        FROM pedido p 
                        JOIN usuario u ON p.id_usuario = u.id 
                        WHERE p.estado = 'pendiente'");
$stmt->execute();
$pedidos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo $twig->render('pedidos-pendientes.twig', [
    'pedidos' => $pedidos,
    'css_url' => '../public/assets/css/style-proveedor.css',
    'session' => $_SESSION
]);
?>