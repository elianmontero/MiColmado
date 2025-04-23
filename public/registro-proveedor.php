<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require 'config.php';

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

    if (empty($nombre_colmado)) {
        $errores['nombre_colmado'] = "El nombre del colmado es obligatorio.";
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
        $stmt_usuario = $conn->prepare("INSERT INTO usuario (nombre_completo, email, contraseña, direccion, telefono, tipo_usuario, cedula) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_usuario->bind_param("sssssss", $nombre, $email, $password_hashed, $direccion, $telefono, $tipo_usuario, $cedula);

        if ($stmt_usuario->execute()) {
            $id_usuario = $stmt_usuario->insert_id;

            // Insertar el colmado en la base de datos
            $stmt_colmado = $conn->prepare("INSERT INTO colmado (nombre_colmado, direccion, id_usuario) VALUES (?, ?, ?)");
            $stmt_colmado->bind_param("ssi", $nombre_colmado, $direccion, $id_usuario);

            if ($stmt_colmado->execute()) {
                // Redirigir a login.php después de un registro exitoso
                header("Location: login.php");
                exit();
            } else {
                $errores['general'] = "Error al registrar el colmado.";
            }

            $stmt_colmado->close();
        } else {
            $errores['general'] = "Error al registrar el usuario.";
        }

        $stmt_usuario->close();
    } else {
        foreach ($errors as $error) {
            echo "<p>$error</p>";
        }
    }

    $check_email->close();
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $direccion = $_POST['direccion'];

    // Limpio y guardo SOLO números
    $telefono = preg_replace('/\D/', '', $_POST['telefono']);
    $cedula = preg_replace('/\D/', '', $_POST['cedula']);

    $tipo_usuario = "proveedor";
    $errores = [];

    // Validar longitud de la cédula
    if (strlen($cedula) !== 11) {
        $errores['cedula'] = "La cédula debe tener 11 números.";
    }

    // Validar longitud del teléfono
    if (strlen($telefono) !== 10) {
        $errores['telefono'] = "El teléfono debe tener 10 números.";
    }

    if (empty($nombre_colmado)) {
        $errores['nombre_colmado'] = "El nombre del colmado es obligatorio.";
    }

    if (empty($errores)) {
        $check_email = $conn->prepare("SELECT id FROM usuario WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $check_email->store_result();

        if ($check_email->num_rows > 0) {
            $errores['email'] = "El correo ya está registrado.";
        } else {
            $stmt_usuario = $conn->prepare("INSERT INTO usuario (nombre_completo, email, contraseña, direccion, telefono, tipo_usuario, cedula) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_usuario->bind_param("sssssss", $nombre, $email, $password, $direccion, $telefono, $tipo_usuario, $cedula);

            if ($stmt_usuario->execute()) {
                $id_usuario = $stmt_usuario->insert_id;

                $stmt_colmado = $conn->prepare("INSERT INTO colmado (nombre_colmado, direccion, id_usuario) VALUES (?, ?, ?)");
                $stmt_colmado->bind_param("ssi", $nombre_colmado, $direccion, $id_usuario);

                if ($stmt_colmado->execute()) {
                    // Redirigir a login.php después de un registro exitoso
                    header("Location: login.php");
                    exit();
                } else {
                    $errores['general'] = "Error al registrarse.";
                }

                $stmt_colmado->close();
            } else {
                $errores['general'] = "Error al registrar el usuario.";
            }

            $stmt_usuario->close();
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
    <link rel="apple-touch-icon" sizes="180x180" href="/public/assets/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/public/assets/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/public/assets/img/favicon-16x16.png">
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
        <h2>Hola 👋, Gracias por trabajar con nosotros, ¿eres nuevo por aquí?</h2>
        <form action="registro-proveedor.php" method="POST">
            <?php if (!empty($errores)): ?>
                <p style="margin-top:30px; color: red; text-align: center;">No se enviaron todos los datos correctamente. Por favor, revisa los campos marcados.</p>
            <?php endif; ?>

            <input placeholder="Nombre completo del propietario" type="text" name="nombre" value="<?php echo isset($nombre) ? $nombre : ''; ?>" required>
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

            <input
                placeholder="Cédula"
                type="text"
                name="cedula"
                id="cedula"
                maxlength="11"
                value="<?php echo isset($cedula) ? $cedula : ''; ?>"
                required
                pattern="^\d{11}$"
                title="Debe contener exactamente 11 números.">
            <?php if (isset($errores['cedula'])): ?>
                <p style="color: red;"><?php echo $errores['cedula']; ?></p>
            <?php endif; ?>
            <br>

            <input placeholder="Contraseña" type="password" name="password" required>
            <?php if (isset($errores['password'])): ?>
                <p style="color: red;"><?php echo $errores['password']; ?></p>
            <?php endif; ?>
            <br>

            <input placeholder="Nombre del colmado" type="text" name="nombre_colmado" value="<?php echo isset($nombre_colmado) ? $nombre_colmado : ''; ?>" required>
            <?php if (isset($errores['nombre_colmado'])): ?>
                <p style="color: red;"><?php echo $errores['nombre_colmado']; ?></p>
            <?php endif; ?>
            <br>

            <input placeholder="Dirección del colmado" type="text" name="direccion" value="<?php echo isset($direccion) ? $direccion : ''; ?>">

            <input type="hidden" name="tipo_usuario" value="proveedor">

            <br>
            <button id="registrarse" type="submit">Registrarse</button>
        </form>

        <?php if (isset($errores['general'])): ?>
            <p style="color: red;"><?php echo $errores['general']; ?></p>
        <?php endif; ?>
        <p id="ini-sesion">¿No eres nuevo por aquí? <a href="login.php">Haz clic aquí</a></p>
        <p id="ini-sesion">¿Quieres registrarte como consumidor? <a href="registro-consumidor.php">Haz clic aquí</a></p>
    </div>

    <script>
        // Formatear cédula
        document.getElementById('cedula').addEventListener('input', function(e) {
            let cedula = e.target.value.replace(/\D/g, ''); // Solo números

            if (cedula.length > 11) {
                cedula = cedula.slice(0, 11);
            }

            e.target.value = cedula;
        });

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
