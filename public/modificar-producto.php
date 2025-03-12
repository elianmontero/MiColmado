<?php 
include '../public/config.php';
session_start();
require_once '../vendor/autoload.php';

// Cargar el cargador de plantillas de Twig
$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

$twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
    return '/assets/' . ltrim($path, '/');
}));

$response = [];

// Detectar si la solicitud es AJAX
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Obtener los detalles del producto
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM PRODUCTO WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $producto = $resultado->fetch_assoc();
}

// Procesar la actualización del producto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $directorio = "../public/assets/imagenes-productos/";

    // Verificar si el directorio existe, sino, crearlo
    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }

    // Lógica para manejar la imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $archivo = $directorio . basename($_FILES["imagen"]["name"]);
        $tipoArchivo = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
        $extensionesPermitidas = array("jpg", "jpeg", "png", "webp");

        if (in_array($tipoArchivo, $extensionesPermitidas)) {
            // Subir la nueva imagen
            if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $archivo)) {
                // Actualizar la imagen en la base de datos
                $sql = "UPDATE PRODUCTO SET nombre = ?, precio = ?, stock = ?, imagen = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $nombre, $precio, $stock, $archivo, $id);
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = "Producto modificado correctamente.";
                } else {
                    $response['success'] = false;
                    $response['message'] = "Error al modificar el producto: " . $stmt->error;
                }
            } else {
                $response['success'] = false;
                $response['message'] = "Error al subir la imagen.";
            }
        } else {
            $response['success'] = false;
            $response['message'] = "Formato de imagen no permitido.";
        }
    } else {
        // Si no se sube una imagen nueva, no modificar el campo de imagen
        $sql = "UPDATE PRODUCTO SET nombre = ?, precio = ?, stock = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nombre, $precio, $stock, $id);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Producto modificado correctamente.";
        } else {
            $response['success'] = false;
            $response['message'] = "Error al modificar el producto: " . $stmt->error;
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
echo $twig->render('modificar-producto.twig', [
    'css_url' => '../public/assets/css/style-proveedor.css',
    'producto' => isset($producto) ? $producto : null,
    'mensaje' => isset($mensaje) ? $mensaje : '',
    'session' => $_SESSION
]);
?>
