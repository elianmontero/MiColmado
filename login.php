<?php
include 'public/config.php';

// Permitir varias sesiones activas por usuario/pestaña
if (isset($_GET['session_name'])) {
    session_name($_GET['session_name']);
} elseif (isset($_POST['session_name'])) {
    session_name($_POST['session_name']);
} elseif (isset($_COOKIE['session_name'])) {
    session_name($_COOKIE['session_name']);
}

// Solo iniciar sesión si ya existe una cookie de sesión personalizada
if (isset($_COOKIE['session_name']) && isset($_COOKIE[$_COOKIE['session_name']])) {
    session_start();
    // Verificar si el usuario ya está logueado en esta sesión personalizada
    if (isset($_SESSION['usuario_id'])) {
        if ($_SESSION['usuario_tipo'] === 'Proveedor') {
            header("Location: public/inicio-proveedor.php?session_name=" . session_name());
            exit();
        } else {
            header("Location: public/inicio-consumidor.php?session_name=" . session_name());
            exit();
        }
    }
} else {
    // No iniciar sesión todavía, mostrar el formulario de login
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Consulta para obtener los datos del usuario
    $stmt = $conn->prepare("SELECT id, nombre_completo, contraseña, tipo_usuario FROM usuario WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $nombre, $hash_password, $tipo_usuario);
    $stmt->fetch();

    // Verifica si el usuario existe y si la contraseña es correcta
    if ($stmt->num_rows > 0) {
        if (password_verify($password, $hash_password)) {
            // Generar un nombre de sesión único por pestaña
            $customSessionName = 'sess_' . bin2hex(random_bytes(8));
            session_name($customSessionName);
            session_start();
            $_SESSION['usuario_id'] = $id;
            $_SESSION['usuario_nombre'] = $nombre;
            $_SESSION['usuario_tipo'] = $tipo_usuario;

            // Guardar el nombre de la sesión en una cookie para esta pestaña
            setcookie('session_name', $customSessionName, 0, '/');

            // Redirigir a la página correspondiente con el nombre de la sesión en la URL
            if ($tipo_usuario === 'Proveedor') {
                header("Location: public/inicio-proveedor.php?session_name=$customSessionName");
            } else {
                header("Location: public/inicio-consumidor.php?session_name=$customSessionName");
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
    <link rel="apple-touch-icon" sizes="180x180" href="/public/assets/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/public/assets/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/public/assets/img/favicon-16x16.png">
    <link rel="stylesheet" href="../public/assets/css/style-forms.css">
</head>
<body>
    <div class="form-container">
        <h2>Hola de nuevo 👋, completa para acceder a tu cuenta</h2>
        <br>
        <form action="login.php" method="POST">
            <input placeholder="Correo electrónico" type="email" name="email" required><br>
            <input placeholder="Contraseña" type="password" name="password" required><br>
            <?php if (isset($error)): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
            <button id="registrarse" type="submit">Iniciar sesión</button>
        </form>
        <p id="ini-sesion">¿Eres nuevo por aquí? <a href="public/registro-consumidor.php">Haz clic aquí</a></p>
    </div>
</body>
</html>
