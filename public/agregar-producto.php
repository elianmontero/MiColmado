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

include '../public/config.php';
require_once '../vendor/autoload.php';

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

$response = [];

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!isset($_SESSION['usuario_id'])) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Sesión expirada. Por favor inicia sesión.']);
        exit();
    } else {
        header("Location: ../login.php");
        exit();
    }
}

$usuario_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT id FROM colmado WHERE id_usuario = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$colmado = $resultado->fetch_assoc();

if (!$colmado) {
    header("Location: registro-proveedor.php");
    exit();
}

$id_colmado = $colmado['id'];

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $contentType = $_SERVER["CONTENT_TYPE"] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            // 🚀 Procesar datos JSON (desde JS)
            $data = json_decode(file_get_contents('php://input'), true);

            $nombre = trim($data['nombre'] ?? '');
            $marca = trim($data['marca'] ?? '');
            $imagen_url = trim($data['imagen_url'] ?? $data['imagen'] ?? '');
            $precio = is_numeric($data['precio'] ?? null) ? floatval($data['precio']) : null;
            $stock = is_numeric($data['stock'] ?? null) ? intval($data['stock']) : null;

            if (empty($nombre) || empty($imagen_url) || $precio === null || $stock === null) {
                throw new Exception('Nombre, imagen, precio y stock son obligatorios.');
            }

            // Si marca no viene, poner cadena vacía
            if ($marca === null) $marca = '';

            // Insertar en la base de datos
            $sql = "INSERT INTO producto (nombre, marca, imagen, precio, stock, id_colmado)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssddi", $nombre, $marca, $imagen_url, $precio, $stock, $id_colmado);

            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Producto personalizado guardado correctamente.'];
            } else {
                throw new Exception('Error SQL: ' . $stmt->error);
            }
        } elseif (strpos($contentType, 'multipart/form-data') !== false || isset($_POST['nombre'])) {
            // 🧾 Procesar formulario clásico
            $nombre = trim($_POST['nombre'] ?? '');
            $precio = is_numeric($_POST['precio'] ?? null) ? floatval($_POST['precio']) : null;
            $stock = is_numeric($_POST['stock'] ?? null) ? intval($_POST['stock']) : null;
            $imagen = $_FILES['imagen'] ?? null;

            if (empty($nombre) || !$imagen || $imagen['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('El nombre y la imagen son obligatorios.');
            }

            // Verificar duplicado
            $stmt = $conn->prepare("SELECT id FROM producto WHERE nombre = ? AND id_colmado = ?");
            $stmt->bind_param("si", $nombre, $id_colmado);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                throw new Exception('El producto ya existe en tu colmado.');
            }

            // Subida de imagen
            $uploadDir = __DIR__ . '/../public/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $extension = pathinfo($imagen['name'], PATHINFO_EXTENSION);
            $uniqueName = uniqid('img_', true) . '.' . $extension;
            $targetPath = $uploadDir . $uniqueName;

            if (!move_uploaded_file($imagen['tmp_name'], $targetPath)) {
                throw new Exception('Error al guardar la imagen.');
            }

            $rutaImagenDB = '/public/uploads/' . $uniqueName;

            // Insertar en la base de datos
            $stmt = $conn->prepare(
                'INSERT INTO producto (nombre, imagen, precio, stock, id_colmado)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->bind_param('ssdii', $nombre, $rutaImagenDB, $precio, $stock, $id_colmado);

            if (!$stmt->execute()) {
                throw new Exception('Error SQL: ' . $stmt->error);
            }

            $response = ['success' => true, 'message' => 'Producto agregado exitosamente.'];
        } else {
            throw new Exception('Tipo de contenido no soportado.');
        }
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

// Si es AJAX, responde con JSON y termina el script
if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Render Twig solo si NO es AJAX
echo $twig->render('agregar-producto.twig', [
    'css_url' => '../public/assets/css/style-proveedor.css',
    'response' => $response,
    'session' => $_SESSION,
    'session_name' => session_name()
]);
?>
