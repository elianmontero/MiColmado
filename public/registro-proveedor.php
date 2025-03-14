<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Cifrado de contraseña
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $tipo_usuario = $_POST['tipo_usuario'];

    // Verificar si el email ya está registrado
    $check_email = $conn->prepare("SELECT id FROM cliente WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();

    if ($check_email->num_rows > 0) {
        echo "El correo ya está registrado.";
    } else {
        // Insertar el usuario en la base de datos
        $stmt = $conn->prepare("INSERT INTO usuario (nombre_completo, email, contraseña, direccion, telefono, tipo_usuario, cedula) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nombre, $email, $password, $direccion, $telefono, $tipo_usuario);

        if ($stmt->execute()) {
            echo "Registro exitoso. <a href='login.php'>Iniciar sesión</a>";
        } else {
            echo "Error en el registro.";
        }
        $stmt->close();
    }
    $check_email->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="../public/assets/css/style-form-proveedor.css">
</head>
<body>
    <div class="form-container">
        <h2>Hola 👋, Gracias por trabajar con nosotros, ¿eres nuevo por aqui?</h2>
        <form action="registro.php" method="POST">

            <input placeholder="Nombre completo" type="text" name="nombre" required><br>

            <input placeholder="Correo electronico" type="email" name="email" required><br>

            <input placeholder="Contraseña" type="password" name="password" required><br>

            <input placeholder="Dirección" type="text" name="direccion">
            <input placeholder="Número de telefono" type="text" name="telefono" required>
            <input placeholder="Cedula" type="number" name="cedula" required>

            <select name="tipo_usuario" required>
                <option id="input-option" value="" disabled selected>Selecciona tu tipo de usuario</option>
                <option value="proveedor">Proveedor</option>
                <option value="consumidor">Consumidor</option>
            </select>
            <br>

            <button id="registrarse" type="submit">Registrarse</button>
        </form>
        <button class="google-login">
            <img src="./assets/img/google_icon.ico" alt="Google" class="google-icon">
            Iniciar Sesión con Google
        </button>
        <p id="ini-sesion">¿No eres nuevo por aqui? <a href="login.php">Haz clic aquí</a></p>
    </div>
</body>
</html>
