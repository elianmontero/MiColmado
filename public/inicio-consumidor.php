<?php
include 'config.php';
session_start();

require_once '../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

$twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
    return '/assets/' . ltrim($path, '/');
}));

// Pasar la información de la sesión a la plantilla
echo $twig->render('home.twig', [
    'css_url' => '../public/assets/css/style.css',
    'session' => $_SESSION
]);
