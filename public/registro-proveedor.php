<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $cedula = $_POST['cedula'];
    $tipo_usuario = $_POST['tipo_usuario'];

    // Validaciones adicionales
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = "La contraseña debe tener al menos 8 caracteres.";
    }

    if (!preg_match("/^\d{10}$/", $telefono)) {
        $errors[] = "El número de teléfono debe tener 10 dígitos.";
    }

    if (!preg_match("/^\d{11}$/", $cedula)) {
        $errors[] = "La cédula debe tener 11 dígitos.";
    }

    // Verificar si el email ya está registrado
    $check_email = $conn->prepare("SELECT id FROM usuario WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();

    if ($check_email->num_rows > 0) {
        $errors[] = "El correo ya está registrado.";
    }

    if (empty($errors)) {
        // Cifrado de contraseña
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        // Insertar el usuario en la base de datos
        $stmt = $conn->prepare("INSERT INTO usuario (nombre_completo, email, contraseña, direccion, telefono, tipo_usuario, cedula) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $nombre, $email, $password_hashed, $direccion, $telefono, $tipo_usuario, $cedula);

        if ($stmt->execute()) {
            // Redirigir al usuario a login.php después de un registro exitoso
            header("Location: login.php");
            exit();
        } else {
            echo "Error en el registro.";
        }
        $stmt->close();
    } else {
        foreach ($errors as $error) {
            echo "<p>$error</p>";
        }
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
    <script>
        function validateLength(input, maxLength) {
            if (input.value.length > maxLength) {
                input.value = input.value.slice(0, maxLength);
            }
        }
    </script>
</head>
<body>
    <div class="form-container">
        <h2>Hola 👋, Gracias por trabajar con nosotros, ¿eres nuevo por aqui?</h2>
        <form action="registro-proveedor.php" method="POST">

            <input placeholder="Nombre completo" type="text" name="nombre" required><br>

            <input placeholder="Correo electronico" type="email" name="email" required><br>

            <input placeholder="Contraseña" type="password" name="password" required minlength="8"><br>

            <input placeholder="Dirección" type="text" name="direccion">
            <input placeholder="Número de telefono" type="text" name="telefono" required maxlength="10" pattern="\d{10}" oninput="validateLength(this, 10)">
            <input placeholder="Cedula" type="number" name="cedula" required maxlength="11" pattern="\d{11}" oninput="validateLength(this, 11)">

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
