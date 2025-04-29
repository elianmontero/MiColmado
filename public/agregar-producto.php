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
        } else {
            // 🧙‍♂️ Procesar formulario clásico
            $nombre = trim($_POST["nombre"] ?? '');
            $precio = $_POST["precio"] ?? '';
            $stock = $_POST["stock"] ?? '';
            $imagen = $_FILES["imagen"] ?? null;

            if (empty($nombre) || !is_numeric($precio) || !is_numeric($stock) || !$imagen) {
                throw new Exception('Datos incompletos o incorrectos en el formulario.');
            }

            $directorio = "../public/assets/imagenes-productos/";
            if (!is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }

            $archivo = $directorio . basename($imagen["name"]);
            $tipoArchivo = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            $extensionesPermitidas = ["jpg", "jpeg", "png", "webp"];

            if (!in_array($tipoArchivo, $extensionesPermitidas)) {
                throw new Exception('El formato de la imagen no es permitido.');
            }

            if (!move_uploaded_file($imagen["tmp_name"], $archivo)) {
                throw new Exception('Error al subir la imagen.');
            }

            $sql = "INSERT INTO producto (nombre, precio, stock, imagen, id_colmado) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $nombre, $precio, $stock, $archivo, $id_colmado);

            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Producto agregado correctamente.'];
            } else {
                throw new Exception('Error al agregar el producto: ' . $stmt->error);
            }
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
