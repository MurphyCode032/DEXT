<?php
session_start();

// Verificar si el usuario no está autenticado
if (!isset($_SESSION['usuario'])) {
    // Redirigir a la página de inicio de sesión
    header("Location: Index.php");
    exit();
}

// Datos de conexión a la base de datos
$host = "localhost";
$usuarioBD = "root";
$contrasenaBD = "";
$nombreBD = "delfinestadistico";

// Variable para almacenar el mensaje
$mensaje = "";

// Obtener el ID de la fila a borrar
$id = $_GET['id'];

// Verificar si se ha enviado el formulario de confirmación
if (isset($_POST['confirmar_borrado'])) {
    if ($_POST['confirmar_borrado'] == 'si') {
        // Intentar la conexión
        $conexion = new mysqli($host, $usuarioBD, $contrasenaBD, $nombreBD);

        // Verificar la conexión
        if ($conexion->connect_error) {
            die("La conexión falló: " . $conexion->connect_error);
        }

        // Iniciar una transacción
        $conexion->begin_transaction();

        try {
            // Sentencia SQL para borrar el registro en la tabla estancia
            $stmt_estancia = $conexion->prepare("DELETE FROM estancia WHERE Alumno_id = ?");
            $stmt_estancia->bind_param("i", $id);
            $stmt_estancia->execute();

            // Sentencia SQL para borrar el registro en la tabla universidadestancia
            $stmt_universidad = $conexion->prepare("DELETE FROM universidadestancia WHERE Alumno_id = ?");
            $stmt_universidad->bind_param("i", $id);
            $stmt_universidad->execute();

            // Sentencia SQL para borrar el registro en la tabla alumnos
            $stmt_alumnos = $conexion->prepare("DELETE FROM alumnos WHERE id = ?");
            $stmt_alumnos->bind_param("i", $id);
            $stmt_alumnos->execute();

            // Confirmar la transacción
            $conexion->commit();

            $mensaje = "Dato borrado exitosamente en todas las tablas.";

            // Retraso de 2 segundos antes de redirigir
            sleep(2);

            // Redirigir a la página de origen
            header("Location: tablauniversidad.php");
            exit();
        } catch (Exception $e) {
            // Revertir la transacción en caso de excepción
            $conexion->rollback();
            $mensaje = "Error al borrar el dato: " . $e->getMessage();
        } finally {
            // Cerrar la conexión y las sentencias
            if (isset($stmt_estancia)) $stmt_estancia->close();
            if (isset($stmt_universidad)) $stmt_universidad->close();
            if (isset($stmt_alumnos)) $stmt_alumnos->close();
            $conexion->close();
        }
    } else {
        // Si el usuario no confirma el borrado, redirigir a la página de origen
        header("Location: tablauniversidad.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto&display=swap">
    <link rel="icon" href="../Assets/alumno.png" type="image/png">
    <script src="buscador.js" defer></script>
    <script src="scripts.js" defer></script>
    <!-- Enlace al nuevo archivo JavaScript -->
    <script src="../Js/responsive.js"></script>
    <title>Confirmar Borrado</title>
    <style>
    body {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    form {
        background-color: #f9f9f9;
        border: 1px solid #ccc;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        width: 300px;
    }

    h2 {
        text-align: center;
    }

    label {
        display: block;
        margin-bottom: 10px;
    }

    input[type="radio"] {
        margin-right: 5px;
    }

    input[type="submit"] {
        background-color: #F58221;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
    }
    </style>
</head>

<body>
    <?php
    // Mostrar el mensaje encima del formulario
    echo '<p>' . $mensaje . '</p>';
    ?>
    <form method="post" action="">
        <h2>¿Estás seguro de que quieres borrar el dato?</h2>
        <label><input type="radio" name="confirmar_borrado" value="si"> Sí</label>
        <label><input type="radio" name="confirmar_borrado" value="no" checked> No</label>
        <br>
        <input class="btn-agregar-alumnos" type="submit" value="Confirmar">
    </form>
</body>

</html>
