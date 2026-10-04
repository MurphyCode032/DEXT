<?php
session_start();

// Verificar si el usuario no está autenticado
if (!isset($_SESSION['usuario'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit();
}

// Datos de conexión a la base de datos
$host = "localhost";
$usuarioBD = "root";
$contrasenaBD = "";
$nombreBD = "Usuarios";

// Intentar la conexión
$conexion = new mysqli($host, $usuarioBD, $contrasenaBD, $nombreBD);

// Verificar la conexión
if ($conexion->connect_error) {
    echo json_encode(['error' => 'La conexión falló: ' . $conexion->connect_error]);
    exit();
}

// Construir la consulta para obtener datos de hombres
$query = "SELECT COUNT(*) as cantidad FROM alumnos WHERE Sexo = 'Hombres'";
$result = $conexion->query($query);

if ($result === false) {
    echo json_encode(['error' => 'Error al ejecutar la consulta: ' . $conexion->error]);
    exit();
}

$data = $result->fetch_assoc();
echo json_encode($data);
