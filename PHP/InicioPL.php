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
$nombreBD = "delfinestadistico";

// Intentar la conexión
$conexion = new mysqli($host, $usuarioBD, $contrasenaBD, $nombreBD);

// Verificar la conexión
if ($conexion->connect_error) {
    die("La conexión falló: " . $conexion->connect_error);
}

// Variable para almacenar mensajes
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['subir'])) {
        $archivo = $_FILES['archivo']['tmp_name'];

        if (empty($archivo)) {
            $mensaje = "Archivo no seleccionado.";
        } else {
            $csv = array_map('str_getcsv', file($archivo));
            $columnas = array_shift($csv); // Obtener las columnas del archivo

            // Validar que las columnas coincidan con la estructura de la base de datos
            $esperadas = array("Tipo_Porgrama", "Fecha_Registro", "Periodo", "Promedio", "Semestre", "Idioma", "Nivel_Idioma", "Documento_Aval", "Fecha_Expiracion");
            if ($columnas != $esperadas) {
                $mensaje = "Archivo incorrecto. Las columnas no coinciden con la estructura esperada.";
            } else {
                // Importar datos a la base de datos
                foreach ($csv as $fila) {
                    $tipoPrograma = $fila[0];
                    $fechaRegistro = $fila[1];
                    $periodo = $fila[2];
                    $promedio = $fila[3];
                    $semestre = $fila[4];
                    $idioma = $fila[5];
                    $nivel_Idioma = $fila[6];
                    $documento_Aval = $fila[7];
                    $fecha_expiración = $fila[8];

                    // Sentencia SQL preparada para evitar inyecciones SQL
                    $stmt = $conexion->prepare("INSERT INTO Alumnos (Tipo_Porgrama, Fecha_Registro, Periodo, Promedio, Semestre, Idioma, Nivel_Idioma, Documento_Aval, Fecha_Expiracion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssssiss", $tipoPrograma, $fechaRegistro, $periodo, $promedio, $semestre, $idioma, $nivel_Idioma, $documento_Aval, $fecha_expiración);
                    $stmt->execute();
                }

                if ($stmt->affected_rows > 0) {
                    $mensaje = "Alumnos subidos exitosamente.";
                } else {
                    $mensaje = "Información no subida, revisar archivo o conexión.";
                }
            }
        }
    } elseif (isset($_POST['importar'])) {
        // ... Resto del código para importar a la base de datos ...
    }
}

// Número de resultados por página
$resultadosPorPagina = 15;

// Página actual
$paginaActual = isset($_GET['pagina']) ? $_GET['pagina'] : 1;

// Calcular el inicio del rango de resultados para la página actual
$inicio = ($paginaActual - 1) * $resultadosPorPagina;



// Consultar datos de la base de datos con límite de resultados por página
// Modifica la consulta SQL para incluir la condición de filtro por ingeniería
$resultado = $conexion->query("SELECT e.*, a.id AS Alumno_id FROM estancia e
                                JOIN alumnos a ON e.Alumno_id = a.id
                                WHERE a.Ingenieria IN ('Ing. en pl?sticos', 'Ingeniería en plásticos', 'Ing. en plásticos')
                                LIMIT $inicio, $resultadosPorPagina");



?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto&display=swap">
    <script src="buscador.js" defer></script>
    <script src="scripts.js" defer></script>


    <title>Archivos Plásticos</title>
    <script>
    // Variable para almacenar la referencia al cuadro de confirmación
    var confirmacionBox;

    // Función que se ejecutará cuando se haga clic en la imagen "User.png"
    function confirmarRedireccion() {
        // Cerrar cualquier cuadro de confirmación activo
        cerrarConfirmacionActiva();

        // Crear el cuadro de confirmación con estilos
        confirmacionBox = document.createElement("div");
        confirmacionBox.className = "confirmacion-box";

        // Contenido del cuadro de confirmación
        confirmacionBox.innerHTML = `
            ¿Estás seguro de que deseas regresar a inicio?
            <br>
            <button class="btn-confirmar" onclick="redirigirIndex()">Confirmar</button>
            <button class="btn-cancelar" onclick="cerrarConfirmacion()">Cancelar</button>
        `;

        // Agregar el cuadro de confirmación al cuerpo del documento
        document.body.appendChild(confirmacionBox);
    }

    // Función para cerrar cualquier cuadro de confirmación activo
    function cerrarConfirmacionActiva() {
        if (confirmacionBox) {
            confirmacionBox.remove();
        }
    }

    // Función para cerrar el cuadro de confirmación
    function cerrarConfirmacion() {
        cerrarConfirmacionActiva();
    }

    // Función para redirigir a Index.php
    function redirigirIndex() {
        window.location.href = 'Index.php';
    }

    // Manejar la selección de archivo y mostrar el nombre del archivo
    function mostrarNombreArchivo(input) {
        const nombreArchivo = document.getElementById('nombre-archivo');
        const file = input.files[0];

        if (file) {
            // Verificar la extensión del archivo
            const extensionesPermitidas = ['.csv', '.xls', '.xlsx'];
            const extension = file.name.slice((file.name.lastIndexOf(".") - 1 >>> 0) + 2);

            if (extensionesPermitidas.includes('.' + extension)) {
                // Mostrar el nombre del archivo seleccionado
                nombreArchivo.textContent = 'Archivo seleccionado: ' + file.name;
            } else {
                alert('Tipo de archivo no permitido. Por favor, selecciona un archivo CSV o Excel.');
                // Limpiar el campo de entrada de archivos y el nombre del archivo
                input.value = '';
                nombreArchivo.textContent = '';
            }
        }
    }

    // Función para cargar el archivo a la base de datos
    function cargarArchivo() {
        // Obtener el archivo seleccionado
        const fileInput = document.getElementById('archivo');
        const file = fileInput.files[0];

        if (file) {
            // Crear un objeto FormData y agregar el archivo
            const formData = new FormData();
            formData.append('archivo', file);

            // Realizar una solicitud AJAX para cargar el archivo
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'inicioC.php', true);

            // Manejar la respuesta de la solicitud
            xhr.onload = function() {
                if (xhr.status === 200) {
                    mostrarMensaje(xhr.responseText, false);
                } else {
                    mostrarMensaje('Error al cargar el archivo.', true);
                }
            };

            // Enviar la solicitud
            xhr.send(formData);
        } else {
            mostrarMensaje('Archivo no seleccionado.', true);
        }
    }

    // Función para mostrar mensajes en la interfaz
    function mostrarMensaje(mensaje, esError) {
        const mensajeElemento = document.getElementById('mensaje');
        mensajeElemento.style.color = esError ? 'red' : 'green';
        mensajeElemento.textContent = mensaje;
    }
    </script>

</head>

<body>
    <header>
        <div class="header-social">
            <a href="https://programadelfin.org.mx/usuarios/contacto-abrir.php" target="_blank">
                <img src="../Assets/gmail.png" alt="Correo Electrónico" class="social-icon">
            </a>
            <span>&nbsp;•&nbsp;</span> <!-- Separador -->
            <a href="https://www.facebook.com/programa.interinstitucional.delfin" target="_blank">
                <img src="../Assets/facebook.png" alt="Facebook" class="social-icon">
            </a>
        </div>
    </header>

    <main>
        <div class="sidebar">
            <a href="https://programadelfin.org.mx/">
                <img src="../Assets/logo.png" alt="Logo" class="logo">
            </a>
            <div class="sidebar-words">
                <a href="https://programadelfin.org.mx/">Inicio</a>
                <a href="https://programadelfin.org.mx/sitio/programa.php">Programa</a>
                <a href="https://programadelfin.org.mx/sitio/estudiantes-verano.php">Estudiantes</a>
                <a href="https://programadelfin.org.mx/sitio/investigadores-encuentro.php">Investigadores</a>
                <a href="https://programadelfin.org.mx/sitio/divulgacion.php">Divulgación</a>
                <a href="graficaPL.php">Filtros</a>
            </div>
            <!-- Información del usuario -->
            <div class="user-info" style="display: flex; align-items: center; margin-right: 2rem;">
                <img src="../Assets/user.png" alt="Usuario" class="user-icon" style="width: 50px; margin-right: 10px;"
                    onclick="confirmarRedireccion()">
                <span class="user-name" style="color: #F58221;"><?php echo $usuario; ?></span>
            </div>
        </div>
    </main>

    <br>
    <br>

    <div style="text-align: center;">
        <div class="sidebar-words">
            <a href="InicioPL.php" class="enlace">Estancia</a>
            <a href="tablauniversidadPL.php" class="enlace">Alumno</a>
            <a href="tablaestanciaPL.php" class="enlace">Universidad</a>
        </div>
    </div>

    <!-- Agregando la tabla con dos botones -->
    <div class="table-container">
        <table border="1">
            <thead>
                <tr>
                    <th>Alumno_id</th>
                    <th>Tipo de programa</th>
                    <th>Fecha de registro</th>
                    <th>Periodo</th>
                    <th>Promedio</th>
                    <th>Semestre</th>
                    <th>Idioma</th>
                    <th>Nivel de idioma</th>
                    <th>Documento aval</th>
                    <th>Fecha de expiración</th>
                </tr>
            </thead>
            <tbody>
                <?php
            if ($resultado->num_rows > 0) {
                // Mostrar los datos en la tabla
                while ($fila = $resultado->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $fila['Alumno_id'] . "</td>";
                    echo "<td>" . $fila['Tipo_Programa'] . "</td>";
                    echo "<td>" . $fila['Fecha_Registro'] . "</td>";
                    echo "<td>" . $fila['Periodo'] . "</td>";
                    echo "<td>" . $fila['Promedio'] . "</td>";
                    echo "<td>" . $fila['Semestre'] . "</td>";
                    echo "<td>" . $fila['Idioma'] . "</td>";
                    echo "<td>" . $fila['Nivel_Idioma'] . "</td>";
                    echo "<td>" . $fila['Documento_Aval'] . "</td>";
                    echo "<td>" . $fila['Fecha_Expiracion'] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='11'>No hay datos disponibles</td></tr>";
            }
            ?>
            </tbody>
        </table>

        <div>
            <!-- Paginación -->
            <?php
                $totalResultados = $conexion->query("SELECT COUNT(*) as total FROM estancia")->fetch_assoc()['total'];
                $totalPaginas = ceil($totalResultados / $resultadosPorPagina);

                // Mostrar enlaces de paginación
                for ($i = 1; $i <= $totalPaginas; $i++) {
                    echo "<a href='inicioPL.php?pagina=$i'>$i</a> ";
                }
                ?>
            <br>
            <br>
            <!-- Botón para abrir el explorador de archivos con la clase btn-agregar-alumnos -->
            <form action="inicioC.php" method="post" enctype="multipart/form-data">

                <input type="file" name="archivo" id="archivo"
                    accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                    onchange="mostrarNombreArchivo(this)" style="display: none;">
                <br>
                <br>

                <br>
                <br>
            </form>



            <!-- Elemento para mostrar el nombre del archivo seleccionado -->
            <span id="nombre-archivo"></span>
            <div id="mensaje" style="margin-top: 10px;"><?php echo $mensaje; ?></div>
        </div>
    </div>

    <footer>
        <div class="footer-text-left">® Programa Delfín</div>
        <div class="footer-text-right">
            Programa Interinstitucional para el Fortalecimiento de la Investigación y el Posgrado del Pacífico
        </div>
    </footer>

    <!-- Enlace al nuevo archivo JavaScript -->
    <script src="../Js/responsive.js"></script>
    <script>
    // Función para borrar una fila por el ID
    function borrarFila(id) {
        if (confirm("¿Estás seguro de que deseas borrar este registro?")) {
            // Realizar una solicitud AJAX para borrar el registro
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'borrar_registro.php', true);

            // Configurar los datos a enviar
            const datos = new FormData();
            datos.append('id', id);

            // Manejar la respuesta de la solicitud
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Recargar la página después de borrar la fila
                    location.reload();
                } else {
                    alert('Error al borrar el registro.');
                }
            };

            // Enviar la solicitud
            xhr.send(datos);
        }
    }
    </script>

</body>

</html>