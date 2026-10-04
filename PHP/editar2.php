<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: Index.php");
    exit();
}

$usuario = $_SESSION['usuario'];

$host = "localhost";
$usuarioBD = "root";
$contrasenaBD = "";
$nombreBD = "delfinestadistico";

$conexion = new mysqli($host, $usuarioBD, $contrasenaBD, $nombreBD);

if ($conexion->connect_error) {
    die("La conexión falló: " . $conexion->connect_error);
}

$mensaje = "";



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['subir-individual'])) {
        // Obtener el ID del alumno a actualizar
        $idAlumno = $_POST['id'];
        //$numControl = $_POST['num_control'];
        $numControl = isset($_POST['num_control']) ? mysqli_real_escape_string($conexion, $_POST['num_control']) : '';
        
        // Validaciones y limpieza de datos (similar a tu código existente)
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
        $fechaNacimiento = mysqli_real_escape_string($conexion, $_POST['fecha_nacimiento']);
        $sexo = mysqli_real_escape_string($conexion, $_POST['sexo']);
        $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
        $correoP = mysqli_real_escape_string($conexion, $_POST['Correo_Personal']);
        $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
        $ingenieria = mysqli_real_escape_string($conexion, $_POST['ingenieria']);
        $estado = mysqli_real_escape_string($conexion, $_POST['estado']);
        $municipio = mysqli_real_escape_string($conexion, $_POST['municipio']);

        // Actualizar Alumnos
        $stmtAlumno = $conexion->prepare("UPDATE Alumnos SET Numero_de_Control=?, Nombre_Completo=?, Fecha_de_Nacimiento=?, Sexo=?, Correo_Institucional=?, Correo_Personal=?, Telefono_Celular=?, Ingenieria=?, Estado=?, Municipio=? WHERE id=?");

        if ($stmtAlumno === false) {
            die("Error en la preparación de la consulta para Alumnos: " . $conexion->error);
        }

        $stmtAlumno->bind_param("ssssssisssi", $numControl, $nombre, $fechaNacimiento, $sexo, $correo, $correoP, $telefono, $ingenieria, $estado, $municipio, $idAlumno);
        $stmtAlumno->execute();

        // Actualizar UniversidadEstancia
        $nombreUniversidad = mysqli_real_escape_string($conexion, $_POST['nombre_universidad']);
        $paisUniversidad = mysqli_real_escape_string($conexion, $_POST['pais']);
        $coordinadorUniversidad = mysqli_real_escape_string($conexion, $_POST['coordinador']);
        $telefonoCoordinadorUniversidad = mysqli_real_escape_string($conexion, $_POST['telefono_coordinador']);
        $correoCoordinadorUniversidad = mysqli_real_escape_string($conexion, $_POST['correo_coordinador']);
        $nombreProyectoInvestigacion = mysqli_real_escape_string($conexion, $_POST['nombre_proyecto']); // opcional
        $observacionesUniversidad = mysqli_real_escape_string($conexion, $_POST['observaciones']); // opcional

        $stmtUniversidad = $conexion->prepare("UPDATE UniversidadEstancia SET Nombre_Universidad=?, Pais=?, Coordinador=?, Telefono_Coordinador=?, Correo_Coordinador=?, Nombre_Proyecto_Investigacion=?, Observaciones=? WHERE Alumno_id=?");

        if ($stmtUniversidad === false) {
            die("Error en la preparación de la consulta para UniversidadEstancia: " . $conexion->error);
        }

        $stmtUniversidad->bind_param("sssssssi", $nombreUniversidad, $paisUniversidad, $coordinadorUniversidad, $telefonoCoordinadorUniversidad, $correoCoordinadorUniversidad, $nombreProyectoInvestigacion, $observacionesUniversidad, $idAlumno);
        $stmtUniversidad->execute();

        // Actualizar Estancia
        $tipoPrograma = mysqli_real_escape_string($conexion, $_POST['tipo_programa']);
        $fechaRegistro = mysqli_real_escape_string($conexion, $_POST['fecha_registro']);
        $periodo = mysqli_real_escape_string($conexion, $_POST['periodo']);
        $promedio = mysqli_real_escape_string($conexion, $_POST['promedio']);
        $semestre = mysqli_real_escape_string($conexion, $_POST['semestre']);
        $idioma = mysqli_real_escape_string($conexion, $_POST['idioma']);
        $nivelIdioma = mysqli_real_escape_string($conexion, $_POST['nivel_idioma']);
        $documentoAval = mysqli_real_escape_string($conexion, $_POST['documento_aval']);
        $fechaExpiracion = mysqli_real_escape_string($conexion, $_POST['fecha_expiracion']);

        // Actualizar Estancia
        $stmtEstancia = $conexion->prepare("UPDATE Estancia SET Tipo_Programa=?, Fecha_Registro=?, Periodo=?, Promedio=?, Semestre=?, Idioma=?, Nivel_Idioma=?, Documento_Aval=?, Fecha_Expiracion=? WHERE Alumno_id=?");

        if ($stmtEstancia === false) {
            die("Error en la preparación de la consulta para Estancia: " . $conexion->error);
        }

        $stmtEstancia->bind_param("ssiiissssi", $tipoPrograma, $fechaRegistro, $periodo, $promedio, $semestre, $idioma, $nivelIdioma, $documentoAval, $fechaExpiracion, $idAlumno);
        $stmtEstancia->execute();

        // Verificar el éxito de las operaciones de actualización
        if ($stmtAlumno->affected_rows > 0 || $stmtUniversidad->affected_rows > 0 || $stmtEstancia->affected_rows > 0) {
            $mensaje = "Información actualizada exitosamente.";
        } else {
            $mensaje = "Error al actualizar la información.";
        }

        // Cerrar los statements
        $stmtAlumno->close();
        $stmtUniversidad->close();
        $stmtEstancia->close();
    }
}


// Cierra la conexión después de realizar todas las operaciones
$conexion->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5;url=tablauniversidad.php">
    <title>Mensaje</title>
</head>
<body>
    <div>
        <?php echo $mensaje; ?>
    </div>
</body>
</html>