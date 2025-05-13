<?php
include './public/config.php';
session_start();
require_once './vendor/autoload.php';

// Cargar el cargador de plantillas de Twig
$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

// Agregar la función 'asset' para usar las rutas de archivos estáticos en las plantillas
$twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
    return '/assets/' . ltrim($path, '/');
}));

$response = [];

// Verificar si hay un usuario autenticado
if (!isset($_SESSION['usuario_id'])) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Sesión expirada. Por favor inicia sesión.']);
        exit();
    } else {
        header("Location: login.php");
        exit();
    }
}

// Obtener el ID del colmado y el id_usuario asociado al usuario en sesión
$usuario_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT id, id_usuario FROM colmado WHERE id_usuario = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$colmado = $resultado->fetch_assoc();

if (!$colmado) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No tienes un colmado registrado.']);
        exit();
    } else {
        header("Location: registro-proveedor.php");
        exit();
    }
}

$id_colmado = $colmado['id'];
$id_usuario_colmado = $colmado['id_usuario']; // Este será igual a $usuario_id, pero lo dejamos explícito

// Detectar si la solicitud es AJAX
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"] ?? '';
    $imagen = $_FILES["imagen"] ?? null;

    // Valores por defecto para productos agregados por la API
    $precio = 1.0;
    $stock = 0;
    $marca = '';

    // Validación de los campos
    if (empty($nombre) || !$imagen) {
        $response['success'] = false;
        $response['message'] = 'El nombre y la imagen son obligatorios.';
    } else {
        // Definir el directorio para guardar las imágenes
        $directorio = "../public/assets/imagenes-productos/";

        // Crear el directorio si no existe
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }

        // Generar nombre único para la imagen
        $extension = strtolower(pathinfo($imagen["name"], PATHINFO_EXTENSION));
        $extensionesPermitidas = array("jpg", "jpeg", "png", "webp");

        if (!in_array($extension, $extensionesPermitidas)) {
            $response['success'] = false;
            $response['message'] = 'El formato de la imagen no es permitido.';
        } else {
            $uniqueName = uniqid('img_', true) . '.' . $extension;
            $archivo = $directorio . $uniqueName;

            // Subir el archivo al servidor
            if (move_uploaded_file($imagen["tmp_name"], $archivo)) {
                $rutaImagenDB = '/public/assets/imagenes-productos/' . $uniqueName;

                // Insertar el producto con precio=1 y stock=0
                $sql = "INSERT INTO producto (nombre, marca, precio, stock, imagen, id_colmado) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdsis", $nombre, $marca, $precio, $stock, $rutaImagenDB, $id_colmado);

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Producto agregado correctamente.';
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Ocurrió un error al agregar el producto: ' . $stmt->error;
                }

                $stmt->close();
            } else {
                $response['success'] = false;
                $response['message'] = 'Hubo un problema al subir la imagen.';
            }
        }
    }

    // Si es una solicitud AJAX, enviamos JSON
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
}

// Si la solicitud es GET o POST sin AJAX, renderizar la plantilla
echo $twig->render('agregar-producto.twig', [
    'css_url' => '../public/assets/css/style-proveedor.css',
    'response' => $response,
    'session' => $_SESSION
]);
?>
