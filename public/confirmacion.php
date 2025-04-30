<!-- filepath: c:\Users\thebe\Desktop\PHP\MiColmado\public\confirmacion.php -->
<?php
session_start();
require_once '../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

echo $twig->render('confirmacion.twig', [
    'css_url' => '../public/assets/css/style-consumidor.css',
    'session' => $_SESSION
]);
?>