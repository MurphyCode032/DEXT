<!--
    *
    * Onix
    * Nombre del Código: [tablauniversidad.php]
    * Fecha de Creación: [10 de diciembre del 2023]
    * Líder de Proyecto: [Roberto Daniel Saavedra Caballero]
    * Revisado por: [Roberto Daniel Saavedra Caballero]
    *
    * Modificaciones:
    * - [2 de enero del 2024] - Folio [01]: [Vinculos actualizados, paginación, creación de tablas, creación de botones y scripts de funcionamiento de botones y tablas, logica de subida de archivos a la base de datos]
    * - [6 de enero del 2024] - Folio [02]: [Mejora responsive de la pagina, creación de lógica de usuario y su nombre, creación de salida de página.]
    *   ...
    *
    * Descripción del Funcionamiento:
    * [Breve descripción del funcionamiento del código, no exceder las 5 líneas]
    *
    -->
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
// Después de crear la conexión
$conexion->set_charset("utf8");

// Variable para almacenar mensajes
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['subir_csv'])) {
        if ($_FILES['archivo_csv']['error'] == UPLOAD_ERR_OK) {
            $nombreArchivo = $_FILES['archivo_csv']['name'];
            $rutaTempArchivo = $_FILES['archivo_csv']['tmp_name'];

            // Procesar el archivo CSV y realizar las inserciones
            $archivo = fopen($rutaTempArchivo, 'r');

            // Variable para rastrear si es la primera fila
            $esPrimeraFila = true;

            while (($fila = fgetcsv($archivo)) !== false) {
                // Omitir la primera fila (encabezados)
                if ($esPrimeraFila) {
                    $esPrimeraFila = false;
                    continue;
                }

                // Insertar en la tabla Alumnos
                $nombre = $fila[0];
                $sexo = $fila[1];
                $numControl = $fila[2];
                $fechaNacimiento = $fila[3];
                $correo = $fila[4];
                $correoP = $fila[5];
                $telefono = $fila[6];
                $ingenieria = $fila[7];
                $estado = $fila[8];
                $municipio = $fila[9];

                // Verificar si ya existe un estudiante con el mismo teléfono o correo
                $stmtVerificar = $conexion->prepare("SELECT id FROM Alumnos WHERE Telefono_Celular = ? OR Correo_Institucional = ?");
                $stmtVerificar->bind_param("ss", $telefono, $correo);
                $stmtVerificar->execute();
                $stmtVerificar->store_result();

                if ($stmtVerificar->num_rows > 0) {
                    $mensaje = "Error: Ya existe un estudiante con el mismo número de teléfono o correo.";
                } else {
                    // Continuar con la inserción en la base de datos
                    $stmtAlumno = $conexion->prepare("INSERT INTO Alumnos (Numero_de_Control, Nombre_Completo, Fecha_de_Nacimiento, Sexo, Correo_Institucional, Correo_Personal, Telefono_Celular, Ingenieria, Estado, Municipio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                    if ($stmtAlumno === false) {
                        die("Error en la preparación de la consulta para Alumnos: " . $conexion->error);
                    }

                    $stmtAlumno->bind_param("ssssssisss", $numControl, $nombre, $fechaNacimiento, $sexo, $correo, $correoP, $telefono, $ingenieria, $estado, $municipio);
                    $stmtAlumno->execute();

                    if ($stmtAlumno->affected_rows > 0) {
                        $idAlumno = $stmtAlumno->insert_id;

                        // Modificación para la inserción en la tabla UniversidadEstancia
                        $nombreUniversidad = $fila[10];
                        $paisUniversidad = $fila[11];
                        $coordinadorUniversidad = $fila[12];
                        $telefonoCoordinadorUniversidad = $fila[13];
                        $correoCoordinadorUniversidad = $fila[14];
                        $nombreProyectoInvestigacion = $fila[15];
                        $observacionesUniversidad = $fila[16];

                        $stmtUniversidad = $conexion->prepare("INSERT INTO UniversidadEstancia (Alumno_id, Nombre_Universidad, Pais, Coordinador, Telefono_Coordinador, Correo_Coordinador, Nombre_Proyecto_Investigacion, Observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

                        if ($stmtUniversidad === false) {
                            die("Error en la preparación de la consulta para UniversidadEstancia: " . $conexion->error);
                        }

                        $stmtUniversidad->bind_param("isssssss", $idAlumno, $nombreUniversidad, $paisUniversidad, $coordinadorUniversidad, $telefonoCoordinadorUniversidad, $correoCoordinadorUniversidad, $nombreProyectoInvestigacion, $observacionesUniversidad);
                        $stmtUniversidad->execute();

                        if ($stmtUniversidad->affected_rows > 0) {
                            // Modificación para la inserción en la tabla Estancia
                            $tipoPrograma = $fila[17];
                            $fechaRegistro = $fila[18];
                            $periodo = $fila[19];
                            $promedio = $fila[20];
                            $semestre = $fila[21];
                            $idioma = $fila[22];
                            $nivelIdioma = $fila[23];
                            $documentoAval = $fila[24];
                            $fechaExpiracion = $fila[25];

                            $stmtEstancia = $conexion->prepare("INSERT INTO Estancia (Alumno_id, Tipo_Programa, Fecha_Registro, Periodo, Promedio, Semestre, Idioma, Nivel_Idioma, Documento_Aval, Fecha_Expiracion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                            if ($stmtEstancia === false) {
                                die("Error en la preparación de la consulta para Estancia: " . $conexion->error);
                            }

                            $stmtEstancia->bind_param("isssiiisss", $idAlumno, $tipoPrograma, $fechaRegistro, $periodo, $promedio, $semestre, $idioma, $nivelIdioma, $documentoAval, $fechaExpiracion);
                            $stmtEstancia->execute();

                            if ($stmtEstancia->affected_rows > 0) {
                                $mensaje = "Información de Estancia subida exitosamente.";
                            } else {
                                $mensaje = "Información de Estancia no subida, revisar formulario o conexión.";
                            }

                            $stmtEstancia->close();
                        } else {
                            $mensaje = "Información de UniversidadEstancia no subida, revisar formulario o conexión.";
                        }

                        $stmtUniversidad->close();
                    } else {
                        $mensaje = "Información de Alumno no subida, revisar formulario o conexión.";
                    }

                    $stmtAlumno->close();
                }

                $stmtVerificar->close();
            }

            fclose($archivo);
            $mensaje = "Datos del archivo CSV subidos exitosamente.";
        } else {
            $mensaje = "Error al subir el archivo CSV.";
        }
    }
}

// ...
// Número de resultados por página
$resultadosPorPagina = 15;

// Página actual
$paginaActual = isset($_GET['pagina']) ? $_GET['pagina'] : 1;

// Calcular el inicio del rango de resultados para la página actual
$inicio = ($paginaActual - 1) * $resultadosPorPagina;

// Consultar datos de la base de datos con límite de resultados por página
$resultado = $conexion->query("SELECT * FROM Alumnos LIMIT $inicio, $resultadosPorPagina");

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


    <title>Archivos</title>
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
            xhr.open('POST', 'inicio.php', true);

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
                <a href="tablauniversidad.php">Regresar a archivos</a>
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

    <style>
    .formulario {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 20px;
    }

    .formulario a {
        text-decoration: none;
        color: #333;
        /* Puedes cambiar el color según tus preferencias */
        background-color: #F58221;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 10px;
        /* Puedes ajustar el margen según tus preferencias */
        display: block;
        width: 200px;
        text-align: center;
        transition: background-color 0.3s ease;
    }

    .formulario a:hover {
        background-color: #ddd;
    }
    </style>

    <div class="formulario">
        <h2 class="login-heading" style="color: #F58221;">Selecciona la carrera a revisar</h2>
        <a href="tablauniversidadS.php" class="btn-agregar-alumnos">Ingeniería de Software</a>
        <a href="tablauniversidadP.php" class="btn-agregar-alumnos">Ingeniería en procesos de producción</a>
        <a href="tablauniversidadPL.php" class="btn-agregar-alumnos">Ingeniería en plásticos</a>
        <a href="tablauniversidadC.php" class="btn-agregar-alumnos">Licenciatura en seguridad ciudadana</a>
    </div>


    <br>
    <br>


    <footer>
        <div class="footer-text-left">® Programa Delfín</div>
        <div class="footer-text-right">
            Programa Interinstitucional para el Fortalecimiento de la Investigación y el Posgrado del Pacífico
        </div>
    </footer>

    <!-- Enlace al nuevo archivo JavaScript -->
    <script src="../Js/responsive.js"></script>


</body>

</html>