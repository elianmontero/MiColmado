<?php
include '../public/config.php';
session_start();
require_once '../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

$twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
    return '/assets/' . ltrim($path, '/');
}));

// Procesar formulario de agregar producto
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"];
    $precio = $_POST["precio"];
    $descripcion = $_POST["descripcion"];
    $stock = $_POST["stock"];
    $directorio = "../public/assets/imagenes/";

    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }

    $archivo = $directorio . basename($_FILES["imagen"]["name"]);
    $tipoArchivo = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
    $extensionesPermitidas = array("jpg", "jpeg", "png", "gif, webp, svg");

    if (in_array($tipoArchivo, $extensionesPermitidas)) {
        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $archivo)) {
            $sql = "INSERT INTO PRODUCTO (id, nombre, precio, descripcion, stock, imagen) VALUES (UUID(), '$nombre', '$precio', '$descripcion', '$stock', '$archivo')";
            if ($conn->query($sql) === TRUE) {
                $mensaje = "Producto agregado correctamente.";
            } else {
                $mensaje = "Error al agregar el producto: " . $conn->error;
            }
        } else {
            $mensaje = "Error al subir la imagen.";
        }
    } else {
        $mensaje = "Formato de imagen no permitido.";
    }
}

echo $twig->render('agregar-producto.twig', [
    'css_url' => '../public/assets/css/style-proveedor.css',
    'mensaje' => isset($mensaje) ? $mensaje : ''
]);
?>