<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require 'config.php';

    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $direccion = $_POST['direccion'];
    $telefono = preg_replace('/\D/', '', $_POST['telefono']); // Solo números
    $tipo_usuario = "consumidor";
    $errores = [];

    // Validaciones adicionales
    if (strlen($password) < 8) {
        $errores['password'] = "La contraseña debe tener al menos 8 caracteres.";
    }

    if (!preg_match("/^\d{10}$/", $telefono)) {
        $errores['telefono'] = "El número de teléfono debe tener 10 dígitos.";
    }

    // Verificar si el email ya está registrado
    $check_email = $conn->prepare("SELECT id FROM usuario WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();

    if ($check_email->num_rows > 0) {
        $errores['email'] = "El correo ya está registrado.";
    }

    if (empty($errores)) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO usuario (nombre_completo, email, contraseña, direccion, telefono, tipo_usuario) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nombre, $email, $password_hashed, $direccion, $telefono, $tipo_usuario);

        if ($stmt->execute()) {
            // Redirigir a login.php con el correo ya introducido
            header("Location: login.php?email=" . urlencode($email));
            exit();
        } else {
            $errores['general'] = "Error al registrarse.";
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
    <meta name="viewport" width="device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/public/assets/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/public/assets/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/public/assets/img/favicon-16x16.png">
    <link rel="stylesheet" href="../public/assets/css/style-forms.css">
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
        <h2>Hola 👋, ¿eres nuevo por aquí?</h2>
        <form action="registro-consumidor.php" method="POST">
            <?php if (!empty($errores)): ?>
                <p style="color: red; text-align: center;">No se enviaron todos los datos correctamente. Por favor, revisa los campos marcados.</p>
            <?php endif; ?>

            <input placeholder="Nombre completo" type="text" name="nombre" value="<?php echo isset($nombre) ? $nombre : ''; ?>" required>
            <?php if (isset($errores['nombre'])): ?>
                <p style="color: red;"><?php echo $errores['nombre']; ?></p>
            <?php endif; ?>
            <br>

            <input placeholder="Correo electrónico" type="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
            <?php if (isset($errores['email'])): ?>
                <p style="color: red;"><?php echo $errores['email']; ?></p>
            <?php endif; ?>
            <br>

            <input
                placeholder="Número de teléfono"
                type="text"
                name="telefono"
                id="telefono"
                maxlength="10"
                value="<?php echo isset($telefono) ? $telefono : ''; ?>"
                required
                pattern="^\d{10}$"
                title="Debe contener exactamente 10 números.">
            <?php if (isset($errores['telefono'])): ?>
                <p style="color: red;"><?php echo $errores['telefono']; ?></p>
            <?php endif; ?>
            <br>

            <input placeholder="Contraseña" type="password" name="password" required>
            <?php if (isset($errores['password'])): ?>
                <p style="color: red;"><?php echo $errores['password']; ?></p>
            <?php endif; ?>
            <br>

            <input placeholder="Dirección" type="text" name="direccion" value="<?php echo isset($direccion) ? $direccion : ''; ?>">

            <input type="hidden" name="tipo_usuario" value="consumidor">

            <br>
            <button id="registrarse" type="submit">Registrarse</button>
        </form>

        <?php if (isset($errores['general'])): ?>
            <p style="color: red;"><?php echo $errores['general']; ?></p>
        <?php endif; ?>

        <p id="ini-sesion">¿No eres nuevo por aquí? <a href="login.php">Haz clic aquí</a></p>
        <p id="ini-sesion">¿Quieres registrar tu colmado? <a href="registro-proveedor.php">Haz clic aquí</a></p>
    </div>

    <script>
        // Formatear teléfono
        document.getElementById('telefono').addEventListener('input', function(e) {
            let telefono = e.target.value.replace(/\D/g, ''); // Solo números

            if (telefono.length > 10) {
                telefono = telefono.slice(0, 10);
            }

            e.target.value = telefono;
        });
    </script>
</body>

</html>