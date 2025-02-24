<?php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Consulta para obtener los datos del usuario
    $stmt = $conn->prepare("SELECT id, nombre, password, rol FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $nombre, $hash_password, $rol);
    $stmt->fetch();

    // Verifica si el usuario existe y si la contraseña es correcta
    if ($stmt->num_rows > 0) {
        if (password_verify($password, $hash_password)) {
            $_SESSION['usuario_id'] = $id;
            $_SESSION['usuario_nombre'] = $nombre;
            $_SESSION['usuario_rol'] = $rol; // Guardamos el rol correctamente

            if ($rol === 'admin') {
                header("Location: admin.php"); // Redirige al panel de admin
            } else {
                header("Location: index.php"); // Redirige a la tienda
            }
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "El correo no está registrado.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="../public/assets/css/style-forms.css">
</head>
<body>
    <div class="form-container">
        <h2>Hola de nuevo 👋, completa para acceder a tu cuenta</h2>
        <br>
        <form action="registro.php" method="POST">

            <input placeholder="Correo electronico" type="email" name="email" required><br>
            <input placeholder="Contraseña" type="password" name="password" required>
            <button id="registrarse" type="submit"><a href="index.php">Iniciar sesión</a></button>
        </form>

        <p id="ini-sesion">¿Eres nuevo por aqui? <a href="registro.php">Haz clic aquí</a></p>
    </div>
</body>
</html>