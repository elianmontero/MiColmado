<?php
// Permitir varias sesiones activas por usuario/pestaña
if (isset($_GET['session_name'])) {
    session_name($_GET['session_name']);
} elseif (isset($_POST['session_name'])) {
    session_name($_POST['session_name']);
} elseif (isset($_COOKIE['session_name'])) {
    session_name($_COOKIE['session_name']);
}
session_start();

include 'config.php';
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

$proveedorId = $_SESSION['usuario_id'];

// Obtener lista de productos activos del proveedor
$sql = "SELECT COUNT(p.id) AS activos
        FROM producto p
        INNER JOIN colmado c ON p.id_colmado = c.id
        WHERE c.id_usuario = ? AND p.stock > 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $proveedorId);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$productosActivos = $res['activos'] ?? 0;
$stmt->close();

// Obtener cantidad de pedidos pendientes del proveedor
$sqlPendientes = "SELECT COUNT(id) AS pendientes
                  FROM pedido
                  WHERE proveedor_id = ? AND estado = 'pendiente'";
$stmtPend = $conn->prepare($sqlPendientes);
$stmtPend->bind_param("i", $proveedorId);
$stmtPend->execute();
$resPend = $stmtPend->get_result()->fetch_assoc();
$pedidosPendientes = $resPend['pendientes'] ?? 0;
$stmtPend->close();

// Calcular ganancias del mes actual para el proveedor
$mesActual = date('m');
$anioActual = date('Y');
$stmt = $conn->prepare("
    SELECT SUM(total) AS ganancias 
    FROM pedido
    WHERE proveedor_id = ? 
    AND MONTH(fecha_pedido) = ? 
    AND YEAR(fecha_pedido) = ?
");
$stmt->bind_param("iii", $proveedorId, $mesActual, $anioActual);
$stmt->execute();
$resultadoGanancias = $stmt->get_result()->fetch_assoc();
$gananciasMes = $resultadoGanancias['ganancias'] ?? 0;
$stmt->close();

echo $twig->render('inicio-proveedor.twig', [
    'productosActivos' => $productosActivos,
    'pedidosPendientes' => $pedidosPendientes,
    'gananciasMes' => $gananciasMes,
    'css_url' => '../public/assets/css/style-proveedor.css',
    'session' => $_SESSION,
    'mensaje' => isset($mensaje) ? $mensaje : '',
    'logout_url' => 'logout.php'
]);
?>
