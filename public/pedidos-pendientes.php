<?php
// Establecer zona horaria local
date_default_timezone_set('America/Santo_Domingo');

include '../public/config.php';
session_start();
require_once '../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

// Obtener el rango exacto del día actual
$inicio = date('Y-m-d 00:00:00');
$fin = date('Y-m-d 23:59:59');

// Consulta con BETWEEN para capturar todo el día
$stmt = $conn->prepare("SELECT p.id, p.total, p.estado, p.fecha_pedido, u.nombre_completo AS usuario_nombre 
                        FROM pedido p 
                        JOIN usuario u ON p.id_usuario = u.id
                        WHERE p.fecha_pedido BETWEEN ? AND ?");
$stmt->bind_param("ss", $inicio, $fin);
$stmt->execute();
$pedidos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Renderizar la plantilla Twig con los datos de los pedidos
echo $twig->render('pedidos-pendientes.twig', [
    'pedidos' => $pedidos,
    'css_url' => '../public/assets/css/style-proveedor.css',
    'session' => $_SESSION
]);
?>