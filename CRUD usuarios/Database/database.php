<?php

$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "crud"; 

//CONFIGURACION DE CONEXION A BASE DE DATOS
$conn = new mysqli($servername, $username, $password, $dbname); 
if ($conn->connect_error) {                                     
die("Connection failed: " . $conn->connect_error);          
}

// Insertar registro
if (isset($_POST['save'])) {
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$sql = "INSERT INTO users (nombre, email) VALUES ('$nombre', '$email')";
$conn->query($sql);
header("Location: index.php");
exit();
}

// Eliminar registro
if (isset($_GET['delete'])) {
$id = $_GET['delete'];
$conn->query("DELETE FROM users WHERE ID=$id");
header("Location: index.php");
exit();
}

// Actualizar registro
if (isset($_POST['update'])) {
$id = $_POST['id'];
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$conn->query("UPDATE users SET nombre='$nombre', email='$email' WHERE ID=$id");
header("Location: index.php");
exit();
}


?>