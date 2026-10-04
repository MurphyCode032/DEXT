<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Archivo CSV o Excel</title>
</head>
<body>
    <form action="otro.php" method="post" enctype="multipart/form-data">
        <label for="archivo">Selecciona un archivo CSV o Excel:</label>
        <input type="file" name="archivo" id="archivo" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">

        <br>

        <input type="submit" name="subir" value="Subir Archivo">
        <input type="submit" name="importar" value="Importar a la base de datos">
    </form>

    <?php
    $host = "localhost";
    $usuario = "root";
    $contrasena = "";
    $nombreBD = "Usuarios";

    $conexion = new mysqli($host, $usuario, $contrasena, $nombreBD);

    if ($conexion->connect_error) {
        die("La conexión falló: " . $conexion->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['subir'])) {
            $archivo = $_FILES['archivo']['tmp_name'];

            if (empty($archivo)) {
                echo "Por favor, selecciona un archivo.";
            } else {
                $csv = array_map('str_getcsv', file($archivo));
                $columnas = array_shift($csv); // Obtener las columnas del archivo

                // Validar que las columnas coincidan con la estructura de la base de datos
                $esperadas = array("Nombre", "Edad", "Sexo", "Estatus", "Carrera", "Residencia");
                if ($columnas != $esperadas) {
                    echo "Las columnas del archivo no coinciden con la estructura esperada.";
                } else {
                    echo "Archivo cargado correctamente. Ahora puedes importarlo a la base de datos.";
                }
            }
        } elseif (isset($_POST['importar'])) {
            $archivo = $_FILES['archivo']['tmp_name'];

            if (empty($archivo)) {
                echo "Por favor, selecciona un archivo.";
            } else {
                $csv = array_map('str_getcsv', file($archivo));
                $columnas = array_shift($csv); // Obtener las columnas del archivo

                // Validar que las columnas coincidan con la estructura de la base de datos
                $esperadas = array("Nombre", "Edad", "Sexo", "Estatus", "Carrera", "Residencia");
                if ($columnas != $esperadas) {
                    echo "Las columnas del archivo no coinciden con la estructura esperada.";
                } else {
                    // Importar datos a la base de datos
                    foreach ($csv as $fila) {
                        $nombre = $fila[0];
                        $edad = $fila[1];
                        $sexo = $fila[2];
                        $estatus = $fila[3];
                        $carrera = $fila[4];
                        $residencia = $fila[5];

                        // Sentencia SQL preparada para evitar inyecciones SQL
                        $stmt = $conexion->prepare("INSERT INTO alumnos (Nombre, Edad, Sexo, Estatus, Carrera, Residencia) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("sissss", $nombre, $edad, $sexo, $estatus, $carrera, $residencia);
                        $stmt->execute();
                    }

                    echo "Datos importados correctamente a la base de datos.";
                }
            }
        }
    }

    $conexion->close();
    ?>
</body>
</html>
