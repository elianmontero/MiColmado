<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require 'config.php';

    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $direccion = $_POST['direccion'];

    // Limpio y guardo SOLO números
    $telefono = preg_replace('/\D/', '', $_POST['telefono']);

    $tipo_usuario = "consumidor";
    $errores = [];

    // Valido longitud
    if (strlen($telefono) !== 10) {
        $errores['telefono'] = "El teléfono debe tener 10 números.";
    }

    if (empty($errores)) {
        $check_email = $conn->prepare("SELECT id FROM usuario WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $check_email->store_result();

        if ($check_email->num_rows > 0) {
            $errores['email'] = "El correo ya está registrado.";
        } else {
            $stmt = $conn->prepare("INSERT INTO usuario (nombre, email, contraseña, direccion, telefono, tipo_usuario) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $nombre, $email, $password, $direccion, $telefono, $tipo_usuario);

            if ($stmt->execute()) {
                echo "Registro exitoso. <a href='login.php'>Iniciar sesión</a>";
            } else {
                $errores['general'] = "Error al registrar el usuario.";
            }

            $stmt->close();
        }

        $check_email->close();
    }

    $conn->close();
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
        <h2>Hola 👋, ¿eres nuevo por aquí?</h2>
        <form action="registro-consumidor.php" method="POST">
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

            <input placeholder="Contraseña" type="password" name="password" required>
            <?php if (isset($errores['password'])): ?>
                <p style="color: red;"><?php echo $errores['password']; ?></p>
            <?php endif; ?>
            <br>

            <input placeholder="Dirección" type="text" name="direccion" value="<?php echo isset($direccion) ? $direccion : ''; ?>">

            <input
                placeholder="Número de teléfono"
                type="text"
                name="telefono"
                id="telefono"
                maxlength="12"
                value="<?php echo isset($telefono) ? $telefono : ''; ?>"
                required
                pattern="^\d{3}-\d{3}-\d{4}$"
                title="Formato requerido: 000-000-0000">
            <?php if (isset($errores['telefono'])): ?>
                <p style="color: red;"><?php echo $errores['telefono']; ?></p>
            <?php endif; ?>

            <br>

            <!-- tipo_usuario oculto -->
            <input type="hidden" name="tipo_usuario" value="consumidor">

            <button id="registrarse" type="submit">Registrarse</button>
        </form>

        <?php if (isset($errores['general'])): ?>
            <p style="color: red;"><?php echo $errores['general']; ?></p>
        <?php endif; ?>
        <button class="google-login">
            <img src="./assets/img/google_icon.ico" alt="Google" class="google-icon">
            Iniciar Sesión con Google
        </button>
        <p id="ini-sesion">¿No eres nuevo por aquí? <a href="login.php">Haz clic aquí</a></p>
        <p id="ini-sesion">¿Quieres registrar tu colmado? <a href="registro-proveedor.php">Haz clic aquí</a></p>
    </div>


    <script>
        document.getElementById('telefono').addEventListener('input', function(e) {
            let telefono = e.target.value.replace(/\D/g, ''); // Elimina cualquier carácter no numérico

            if (telefono.length > 10) {
                telefono = telefono.slice(0, 10); // Limita el número a 10 dígitos
            }

            // Aplica el formato 000-000-0000
            let formatted = '';
            if (telefono.length >= 3) {
                formatted += telefono.slice(0, 3) + '-';
            }
            if (telefono.length >= 6) {
                formatted += telefono.slice(3, 6) + '-' + telefono.slice(6);
            } else if (telefono.length > 3) {
                formatted += telefono.slice(3);
            }

            e.target.value = formatted; // Actualiza el valor del campo con el formato
        });
    </script>

</body>

</html>