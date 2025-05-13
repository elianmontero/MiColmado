<!-- filepath: c:\Users\thebe\Desktop\PHP\MiColmado\public\confirmacion.php -->
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
require_once '../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

echo $twig->render('confirmacion.twig', [
    'css_url' => '../public/assets/css/style-consumidor.css',
    'session' => $_SESSION
]);
?>