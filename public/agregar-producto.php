<?php
include '../public/config.php';
session_start();
require_once '../vendor/autoload.php';

// Cargar el cargador de plantillas de Twig
$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

// Agregar la función 'asset' para usar las rutas de archivos estáticos en las plantillas
$twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
    return '/assets/' . ltrim($path, '/');
}));

$response = [];

// Detectar si la solicitud es AJAX
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"] ?? '';
    $precio = $_POST["precio"] ?? '';
    $descripcion = $_POST["descripcion"] ?? '';
    $stock = $_POST["stock"] ?? '';
    $imagen = $_FILES["imagen"] ?? null;

    // Validación de los campos
    if (empty($nombre) || empty($precio) || empty($stock) || !$imagen) {
        $response['success'] = false;
        $response['message'] = 'Hubo un problema al enviar el formulario.';
    } else {
        $directorio = "../public/assets/imagenes-productos/";

        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $archivo = $directorio . basename($_FILES["imagen"]["name"]);
        $tipoArchivo = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
        $extensionesPermitidas = array("jpg", "jpeg", "png", "webp");

        if (!in_array($tipoArchivo, $extensionesPermitidas)) {
            $response['success'] = false;
            $response['message'] = 'El formato de la imagen no es permitido.';
        } else {
            if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $archivo)) {
                $sql = "INSERT INTO PRODUCTO (id, nombre, precio, descripcion, stock, imagen) VALUES (UUID(), ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $nombre, $precio, $descripcion, $stock, $archivo);

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Producto agregado correctamente.';
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Ocurrió un error al agregar el producto.';
                }
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
        exit;
    }
}

// Si la solicitud es GET o POST sin AJAX, renderizar la plantilla
echo $twig->render('agregar-producto.twig', [
    'css_url' => '../public/assets/css/style-proveedor.css',
    'response' => $response,
    'session' => $_SESSION
]);

?>
