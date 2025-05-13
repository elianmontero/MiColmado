<?php
// Establecer zona horaria local
date_default_timezone_set('America/Santo_Domingo');

// Permitir varias sesiones activas por usuario/pestaña
if (isset($_GET['session_name'])) {
    session_name($_GET['session_name']);
} elseif (isset($_POST['session_name'])) {
    session_name($_POST['session_name']);
} elseif (isset($_COOKIE['session_name'])) {
    session_name($_COOKIE['session_name']);
}
session_start();

include '../public/config.php';
require_once '../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

// Obtener el rango exacto del día actual
$inicio = date('Y-m-d 00:00:00');
$fin = date('Y-m-d 23:59:59');

// Obtener el id del proveedor desde la sesión
$proveedorId = $_SESSION['usuario_id'] ?? null;

// Consulta con BETWEEN para capturar todo el día y filtrar por proveedor
$stmt = $conn->prepare("SELECT p.id, p.total, p.estado, p.fecha_pedido, p.direccion, u.nombre_completo AS usuario_nombre 
                        FROM pedido p 
                        JOIN usuario u ON p.id_usuario = u.id
                        WHERE p.fecha_pedido BETWEEN ? AND ? AND p.proveedor_id = ?");
$stmt->bind_param("ssi", $inicio, $fin, $proveedorId);
$stmt->execute();
$pedidos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$twig->addFunction(new \Twig\TwigFunction('session_url', function ($url) {
    $sessionName = session_name();
    $sessionId = session_id();
    if (!$sessionName || !$sessionId) return $url;
    $sep = (strpos($url, '?') !== false) ? '&' : '?';
    return $url . $sep . 'session_name=' . $sessionName;
}));

// Renderizar la plantilla Twig con los datos de los pedidos
echo $twig->render('pedidos-pendientes.twig', [
    'pedidos' => $pedidos,
    'css_url' => '../public/assets/css/style-proveedor.css',
    'session' => $_SESSION,
    'session_name' => session_name()
]);
?>