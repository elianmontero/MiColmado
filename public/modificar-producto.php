<?php
include '../public/config.php';
session_start();
require_once '../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

$twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
    return '/assets/' . ltrim($path, '/');
}));

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM PRODUCTO WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $producto = $resultado->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $descripcion = $_POST['descripcion'];
    $stock = $_POST['stock'];
    $directorio = "../public/assets/imagenes-productos/";

    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }

    $archivo = $directorio . basename($_FILES["imagen"]["name"]);
    $tipoArchivo = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
    $extensionesPermitidas = array("jpg", "jpeg", "png", "webp");

    if (in_array($tipoArchivo, $extensionesPermitidas)) {
        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $archivo)) {
            $sql = "UPDATE PRODUCTO SET nombre = ?, precio = ?, descripcion = ?, stock = ?, imagen = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $nombre, $precio, $descripcion, $stock, $archivo, $id);
            if ($stmt->execute()) {
                $mensaje = "Producto modificado correctamente.";
            } else {
                $mensaje = "Error al modificar el producto: " . $conn->error;
            }
        } else {
            $mensaje = "Error al subir la imagen.";
        }
    } else {
        $mensaje = "Formato de imagen no permitido.";
    }
}

echo $twig->render('modificar-producto.twig', [
    'css_url' => '../public/assets/css/style-proveedor.css',
    'producto' => isset($producto) ? $producto : null,
    'mensaje' => isset($mensaje) ? $mensaje : '',
    'session' => $_SESSION
]);
?>