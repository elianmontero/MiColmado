<?php
include '../public/config.php';
session_start();
require_once '../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

$twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
    return '/assets/' . ltrim($path, '/');
}));

$response = [];

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
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

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $contentType = $_SERVER["CONTENT_TYPE"] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            // 🚀 Procesar datos JSON (desde JS)
            $data = json_decode(file_get_contents('php://input'), true);

            $nombre = trim($data['nombre'] ?? '');
            $marca = trim($data['marca'] ?? '');
            $imagen_url = trim($data['imagen_url'] ?? '');
            $precio = $data['precio'] ?? 0;
            $stock = $data['stock'] ?? 0;

            // Validar los datos
            if (empty($nombre) || empty($marca) || empty($imagen_url) || $precio <= 0 || $stock <= 0) {
                throw new Exception('Datos incompletos o incorrectos para producto personalizado.');
            }

            // Insertar el producto en la base de datos
            $sql = "INSERT INTO producto (nombre, marca, imagen, precio, stock, id_colmado) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssdii", $nombre, $marca, $imagen_url, $precio, $stock, $id_colmado);

            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Producto personalizado guardado correctamente.'];
            } else {
                throw new Exception('Error SQL: ' . $stmt->error);
            }
        } elseif (strpos($contentType, 'multipart/form-data') !== false) {
            // Procesar datos enviados desde el formulario clásico
            $nombre = trim($_POST['nombre'] ?? '');
            $imagen = $_FILES['imagen'] ?? null;

            if (empty($nombre) || !$imagen || $imagen['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Datos incompletos o imagen inválida.');
            }

            // Verificar si el producto ya existe en el colmado
            $stmt = $conn->prepare("SELECT id FROM producto WHERE nombre = ? AND id_colmado = ?");
            $stmt->bind_param("si", $nombre, $id_colmado);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                throw new Exception('El producto ya existe en tu colmado.');
            }

            // Generar un nombre único para la imagen
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
                'INSERT INTO producto (nombre, imagen, id_colmado)
                 VALUES (?, ?, ?)'
            );
            $stmt->bind_param('ssi', $nombre, $rutaImagenDB, $id_colmado);

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

// Si es AJAX, responde con JSON
if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Si es formulario clásico, renderizamos la página Twig
echo $twig->render('agregar-producto.twig', [
    'css_url' => '../public/assets/css/style-proveedor.css',
    'response' => $response,
    'session' => $_SESSION
]);
?>
