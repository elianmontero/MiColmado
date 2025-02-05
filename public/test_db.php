<?php
include 'config.php';

if ($conn) {
    echo "Conexión exitosa a la base de datos.";
} else {
    echo "Error en la conexión.";
}
?>
