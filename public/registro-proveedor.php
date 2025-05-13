<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require 'config.php';

    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $cedula = $_POST['cedula'];
    $tipo_usuario = $_POST['tipo_usuario'];
    $nombre_colmado = $_POST['nombre_colmado'];

    // Validaciones adicionales
    $errores = [];

    if (strlen($password) < 8) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres.";
    }

    if (!preg_match("/^\d{10}$/", $telefono)) {
        $errores[] = "El número de teléfono debe tener 10 dígitos.";
    }

    if (!preg_match("/^\d{11}$/", $cedula)) {
        $errores[] = "La cédula debe tener 11 dígitos.";
    }

    if (empty($nombre_colmado)) {
        $errores[] = "El nombre del colmado es obligatorio.";
    }

    // Verificar si el email ya está registrado
    $check_email = $conn->prepare("SELECT id FROM usuario WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();

    if ($check_email->num_rows > 0) {
        $errores[] = "El correo ya está registrado.";
    }

    if (empty($errores)) {
        // Cifrado de contraseña
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        // Insertar el usuario en la base de datos
        $stmt_usuario = $conn->prepare("INSERT INTO usuario (nombre_completo, email, contraseña, direccion, telefono, tipo_usuario, cedula) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_usuario->bind_param("sssssss", $nombre, $email, $password_hashed, $direccion, $telefono, $tipo_usuario, $cedula);

        if ($stmt_usuario->execute()) {
            $id_usuario = $stmt_usuario->insert_id;

            // Insertar el colmado en la base de datos
            $stmt_colmado = $conn->prepare("INSERT INTO colmado (nombre_colmado, direccion, id_usuario) VALUES (?, ?, ?)");
            $stmt_colmado->bind_param("ssi", $nombre_colmado, $direccion, $id_usuario);

            if ($stmt_colmado->execute()) {
                // Redirigir a login.php después de un registro exitoso
                header("Location: /login.php");
                exit();
            } else {
                $errores[] = "Error al registrar el colmado.";
            }

            $stmt_colmado->close();
        } else {
            $errores[] = "Error al registrar el usuario.";
        }

        $stmt_usuario->close();
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
    <link rel="apple-touch-icon" sizes="180x180" href="/public/assets/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/public/assets/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/public/assets/img/favicon-16x16.png">
    <link rel="stylesheet" href="../public/assets/css/style-form-proveedor.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <h2>Hola 👋, Gracias por trabajar con nosotros, ¿eres nuevo por aquí?</h2>
        <form action="registro-proveedor.php" method="POST">
            <input placeholder="Nombre completo del propietario" type="text" name="nombre" value="<?php echo isset($nombre) ? $nombre : ''; ?>" required>
            <br>
            <input placeholder="Correo electrónico" type="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
            <br>
            <input placeholder="Número de teléfono" type="text" name="telefono" id="telefono" maxlength="10" value="<?php echo isset($telefono) ? $telefono : ''; ?>" required pattern="^\d{10}$" title="Debe contener exactamente 10 números.">
            <br>
            <input placeholder="Cédula" type="text" name="cedula" id="cedula" maxlength="11" value="<?php echo isset($cedula) ? $cedula : ''; ?>" required pattern="^\d{11}$" title="Debe contener exactamente 11 números.">
            <br>
            <input placeholder="Contraseña" type="password" name="password" required>
            <br>
            <input placeholder="Nombre del colmado" type="text" name="nombre_colmado" value="<?php echo isset($nombre_colmado) ? $nombre_colmado : ''; ?>" required>
            <br>
            <input placeholder="Dirección del colmado" type="text" name="direccion" value="<?php echo isset($direccion) ? $direccion : ''; ?>">
            <input type="hidden" name="tipo_usuario" value="proveedor">
            <br>
            <button id="registrarse" type="submit">Registrarse</button>
        </form>
        <p id="ini-sesion">¿No eres nuevo por aquí? <a href="/login.php">Haz clic aquí</a></p>
        <p id="ini-sesion">¿Quieres registrarte como consumidor? <a href="registro-consumidor.php">Haz clic aquí</a></p>
    </div>
    <script>
        // Formatear cédula
        document.getElementById('cedula').addEventListener('input', function(e) {
            let cedula = e.target.value.replace(/\D/g, '');
            if (cedula.length > 11) {
                cedula = cedula.slice(0, 11);
            }
            e.target.value = cedula;
        });
        // Formatear teléfono
        document.getElementById('telefono').addEventListener('input', function(e) {
            let telefono = e.target.value.replace(/\D/g, '');
            if (telefono.length > 10) {
                telefono = telefono.slice(0, 10);
            }
            e.target.value = telefono;
        });

        // Mostrar errores con SweetAlert
        <?php if (!empty($errores)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error en el registro',
                html: `<?php foreach ($errores as $error) { echo htmlspecialchars($error) . "<br>"; } ?>`
            });
        <?php endif; ?>
    </script>
</body>
</html>