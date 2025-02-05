<?php
require_once '../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

$twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
  return '/assets/' . ltrim($path, '/');
}));

$products = [
    [
        'name' => 'Mango ',
        'price' => '25RD$',
        'image' => './assets/img/mango.webp'
    ],
    [
        'name' => 'Producto 2',
        'price' => '$20.00',
        'image' => '/assets/img/product2.jpg'
    ],
    [
      'name' => 'Producto 2',
      'price' => '$20.00',
      'image' => '/assets/img/product2.jpg'
    ],
    [
    'name' => 'Producto 2',
    'price' => '$20.00',
    'image' => '/assets/img/product2.jpg'
    ],
    [
        'name' => 'Producto 2',
        'price' => '$20.00',
        'image' => './assets/img/mango.webp'
    ],
    
    // Agrega más productos aquí
];

echo $twig->render('home.twig', ['products' => $products]);
