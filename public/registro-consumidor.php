<?php
include 'config.php';

$errores = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $direccion = trim($_POST['direccion']);
    $telefono = trim($_POST['telefono']);
    $cedula = isset($_POST['cedula']) ? trim($_POST['cedula']) : '';
    $tipo_usuario = trim($_POST['tipo_usuario']);

    // Validar los campos
    if (empty($nombre)) {
        $errores['nombre'] = "El nombre es obligatorio.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = "El correo electrónico no es válido.";
    }
    if (strlen($password) < 6) {
        $errores['password'] = "La contraseña debe tener al menos 6 caracteres.";
    }
    if (empty($telefono)) {
        $errores['telefono'] = "El número de teléfono es obligatorio.";
    }
    if (empty($cedula) || strlen($cedula) != 11 || !ctype_digit($cedula)) {
        $errores['cedula'] = "La cédula es incorrecta.";
    }
    if (empty($tipo_usuario)) {
        $errores['tipo_usuario'] = "El tipo de usuario es obligatorio.";
    }

    // Verificar si el email ya está registrado
    $check_email = $conn->prepare("SELECT id FROM cliente WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();

    if ($check_email->num_rows > 0) {
        $errores['email'] = "El correo ya está registrado.";
    }

    if (empty($errores)) {
        // Cifrado de contraseña
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        // Insertar el usuario en la base de datos
        $stmt = $conn->prepare("INSERT INTO cliente (nombre_completo, cedula, email, contraseña, direccion, telefono, tipo_usuario) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $nombre, $cedula, $email, $password_hashed, $direccion, $telefono, $tipo_usuario);

        if ($stmt->execute()) {
            // Redirigir al inicio de consumidor si el registro es exitoso
            header("Location: login.php");
            exit();
        } else {
            $errores['general'] = "Error en el registro.";
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
    <link rel="stylesheet" href="../public/assets/css/style-forms.css">
</head>
<body>
    <div class="form-container">
        <h2>Hola 👋, ¿eres nuevo por aqui?</h2>
        <form action="registro-consumidor.php" method="POST">
            <input placeholder="Nombre completo" type="text" name="nombre" value="<?php echo isset($nombre) ? $nombre : ''; ?>" required>
            <?php if (isset($errores['nombre'])): ?>
                <p style="color: red;"><?php echo $errores['nombre']; ?></p>
            <?php endif; ?>
            <br>

            <input placeholder="Correo electronico" type="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
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
            <input placeholder="Número de telefono" type="text" name="telefono" value="<?php echo isset($telefono) ? $telefono : ''; ?>" required>
            <?php if (isset($errores['telefono'])): ?>
                <p style="color: red;"><?php echo $errores['telefono']; ?></p>
            <?php endif; ?>
            <br>

            <input placeholder="Cédula" type="text" name="cedula" value="<?php echo isset($cedula) ? $cedula : ''; ?>" required>
            <?php if (isset($errores['cedula'])): ?>
                <p style="color: red;"><?php echo $errores['cedula']; ?></p>
            <?php endif; ?>
            <br>

            <select name="tipo_usuario" required>
                <option id="input-option" value="" disabled selected>Selecciona tu tipo de usuario</option>
                <option value="proveedor" <?php echo (isset($tipo_usuario) && $tipo_usuario == 'proveedor') ? 'selected' : ''; ?>>Proveedor</option>
                <option value="consumidor" <?php echo (isset($tipo_usuario) && $tipo_usuario == 'consumidor') ? 'selected' : ''; ?>>Consumidor</option>
            </select>
            <?php if (isset($errores['tipo_usuario'])): ?>
                <p style="color: red;"><?php echo $errores['tipo_usuario']; ?></p>
            <?php endif; ?>
            <br>

            <button id="registrarse" type="submit">Registrarse</button>
        </form>
        <?php if (isset($errores['general'])): ?>
            <p style="color: red;"><?php echo $errores['general']; ?></p>
        <?php endif; ?>
        <button class="google-login">
            <img src="./assets/img/google_icon.ico" alt="Google" class="google-icon">
            Iniciar Sesión con Google
        </button>
        <p id="ini-sesion">¿No eres nuevo por aqui? <a href="login.php">Haz clic aquí</a></p>
        <p id="ini-sesion">¿Quieres registrar tu colmado? <a href="registro-proveedor.php">Haz clic aquí</a></p>
    </div>
</body>
</html>
