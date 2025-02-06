<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Cifrado de contraseña

    // Verificar si el email ya está registrado
    $check_email = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();

    if ($check_email->num_rows > 0) {
        echo "El correo ya está registrado.";
    } else {
        // Insertar el usuario en la base de datos
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $email, $password);

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
    <link rel="stylesheet" href="../public/assets/css/style-forms.css">
</head>
<body>
    <div class="form-container">
        <h2>Registra tu cuenta</h2>
        <form action="registro.php" method="POST">

            <input placeholder="Nombre" type="text" name="nombre" required><br>

            <input placeholder="Correo electronico" type="email" name="email" required><br>

            <input placeholder="Contraseña" type="password" name="password" required><br>

            <input placeholder="Dirección" type="text" name="direccion">
            <br>
            <button type="submit">Registrarse</button>
        </form>
    </div>
</body>
</html>
