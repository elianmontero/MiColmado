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
    <title>Iniciar Sesión</title>
</head>
<body>
    <h2>Inicio de Sesión</h2>

    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form action="login.php" method="POST">
        <label>Email:</label>
        <input type="email" name="email" required><br>

        <label>Contraseña:</label>
        <input type="password" name="password" required><br>

        <button type="submit">Iniciar Sesión</button>
    </form>
</body>
</html>
