<?php
$host = "localhost"; // Cambia tu_host con la dirección del servidor de la base de datos
$usuario = "root"; // Cambia tu_usuario con el nombre de usuario de la base de datos
$contrasena = ""; // Cambia tu_contrasena con la contraseña de la base de datos
$nombreBD = "usuarios"; // Cambia tu_base_de_datos con el nombre de la base de datos

// Intentar la conexión
$conexion = new mysqli($host, $usuario, $contrasena, $nombreBD);

// Verificar la conexión
if ($conexion->connect_error) {
    die("La conexión falló: " . $conexion->connect_error);
} else {
    //echo "Conexión exitosa";
}


?>
