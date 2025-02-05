<?php
$host = "localhost"; // Servidor (localhost en XAMPP)
$usuario = "root";   // Usuario por defecto en XAMPP
$password = "";      // Sin contraseña por defecto en XAMPP
$base_datos = "inventario_tienda"; // Nombre de la base de datos

// Conexión a la base de datos
$conn = new mysqli($host, $usuario, $password, $base_datos);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

?>
