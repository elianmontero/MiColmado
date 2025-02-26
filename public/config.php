<?php
$host = "localhost";
$usuario = "root"; // Cambia esto si tienes otro usuario
$password = "";
$base_datos = "MiColmado";

// Conexión a la base de datos
$conn = new mysqli($host, $usuario, $password, $base_datos);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>