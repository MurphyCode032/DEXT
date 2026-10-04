<?php
session_start();

// Verificar si el usuario no está autenticado
if (!isset($_SESSION['usuario'])) {
    // Redirigir a la página de inicio de sesión
    header("Location: Index.php");
    exit();
}

// Obtener el nombre de usuario de la sesión
$usuario = $_SESSION['usuario'];

// Datos de conexión a la base de datos
$host = "localhost";
$usuarioBD = "root";
$contrasenaBD = "";
$nombreBD = "Usuarios";

// Intentar la conexión
$conexion = new mysqli($host, $usuarioBD, $contrasenaBD, $nombreBD);

// Verificar la conexión
if ($conexion->connect_error) {
    die("La conexión falló: " . $conexion->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['busqueda'])) {
    $busqueda = $_GET['busqueda'];

    // Consultar datos filtrados de la base de datos
    $consulta = $conexion->prepare("SELECT * FROM alumnos WHERE Nombre LIKE ?");
    
    // Verificar si la preparación de la consulta fue exitosa
    if (!$consulta) {
        die("Error en la preparación de la consulta: " . $conexion->error);
    }

    $busqueda_param = "%$busqueda%";
    $consulta->bind_param("s", $busqueda_param);
    
    // Verificar si la ejecución de la consulta fue exitosa
    if (!$consulta->execute()) {
        die("Error en la ejecución de la consulta: " . $consulta->error);
    }

    $resultado = $consulta->get_result();

    // Envolver los resultados en una tabla HTML
    echo "<table border='1'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Nombre</th>";
    echo "<th>Edad</th>";
    echo "<th>Sexo</th>";
    echo "<th>Estatus</th>";
    echo "<th>Carrera</th>";
    echo "<th>Residencia</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";

    // Mostrar los resultados en formato HTML
    while ($fila = $resultado->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $fila['Nombre'] . "</td>";
        echo "<td>" . $fila['Edad'] . "</td>";
        echo "<td>" . $fila['Sexo'] . "</td>";
        echo "<td>" . $fila['Estatus'] . "</td>";
        echo "<td>" . $fila['Carrera'] . "</td>";
        echo "<td>" . $fila['Residencia'] . "</td>";
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";

    $resultado->close();
    $consulta->close();
}

// Cerrar la conexión a la base de datos de manera segura
$conexion->close();
?>
