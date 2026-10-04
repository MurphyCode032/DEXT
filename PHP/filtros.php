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
            $esperadas = array("Nombre", "Edad", "Sexo", "Estatus", "Carrera", "Residencia");
            if ($columnas != $esperadas) {
                $mensaje = "Archivo incorrecto. Las columnas no coinciden con la estructura esperada.";
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
$resultado = $conexion->query("SELECT * FROM alumnos LIMIT $inicio, $resultadosPorPagina");

function obtenerDatosFiltrados($conexion, $filtro) {
    $filtroSQL = ""; // Debes completar esto según la estructura de tu base de datos
    // Aquí deberías construir la parte del WHERE para filtrar por género, carrera, etc.

    $query = "SELECT * FROM alumnos $filtroSQL LIMIT $inicio, $resultadosPorPagina";
    $result = $conexion->query($query);

    $datos = array();
    while ($row = $result->fetch_assoc()) {
        $datos[] = $row;
    }

    return $datos;
}

// Consultar la cantidad de hombres y mujeres
$querySexo = "SELECT Sexo, COUNT(*) as cantidad FROM alumnos GROUP BY Sexo";
$resultadoSexo = $conexion->query($querySexo);

// Inicializar arrays para almacenar los datos
$sexoLabels = [];
$cantidadDatosSexo = []; // Cambié el nombre para evitar conflicto

// Obtener los datos de la consulta
while ($filaSexo = $resultadoSexo->fetch_assoc()) {
    $sexoLabels[] = $filaSexo['Sexo'];
    $cantidadDatosSexo[] = $filaSexo['cantidad'];
}


// Consultar la cantidad de alumnos por carrera
$queryCarrera = "SELECT Carrera, COUNT(*) as cantidad FROM alumnos GROUP BY Carrera";
$resultadoCarrera = $conexion->query($queryCarrera);

// Inicializar arrays para almacenar los datos
$carreraLabels = [];
$cantidadDatosCarrera = [];

// Obtener los datos de la consulta
while ($filaCarrera = $resultadoCarrera->fetch_assoc()) {
    $carreraLabels[] = $filaCarrera['Carrera'];
    $cantidadDatosCarrera[] = $filaCarrera['cantidad'];
}

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    google.charts.load('current', {
        'packages': ['corechart']
    });
    google.charts.setOnLoadCallback(function() {
        // Aquí no es necesario hacer nada, solo cargar la biblioteca
    });
    </script>
    <title>Filtros</title>
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
                <a href="alumno.php">Agregar alumno</a>
                <a href="inicio.php">Archivos</a>
                <script src="https://www.gstatic.com/charts/loader.js"></script>
                <script>
                google.charts.load('current', {
                    'packages': ['corechart']
                });
                google.charts.setOnLoadCallback(function() {
                    // Aquí no es necesario hacer nada, solo cargar la biblioteca
                });
                </script>

            </div>
            <!-- Información del usuario -->
            <div class="user-info" style="display: flex; align-items: center; margin-right: 2rem;">
                <img src="../Assets/user.png" alt="Usuario" class="user-icon" style="width: 50px; margin-right: 10px;"
                    onclick="confirmarRedireccion()">
                <span class="user-name" style="color: #F58221;"><?php echo $usuario; ?></span>

            </div>

        </div>

    </main>
    <script>
    // ... (código anterior) ...

    // Función para mostrar la selección en la gráfica
    function mostrarSeleccion() {
        // Obtener el valor seleccionado del menú desplegable
        var filtroSeleccionado = document.getElementById('opcionesFiltro').value;

        // Realizar acciones según el filtro seleccionado
        switch (filtroSeleccionado) {
            case 'opcion1':
                // Actualizar y mostrar la gráfica de Hombres y Mujeres
                var ctx = document.getElementById('graficaPastel').getContext('2d');
                ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                var graficaPastel = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: <?php echo json_encode($sexoLabels); ?>,
                        datasets: [{
                            data: <?php echo json_encode($cantidadDatosSexo); ?>,
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.7)', // Color para Hombres
                                'rgba(54, 162, 235, 0.7)' // Color para Mujeres
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        title: {
                            display: true,
                            text: 'Distribución de Hombres y Mujeres'
                        }
                    }
                });
                break;
            case 'opcion2':
                // Realizar una solicitud AJAX para cargar la gráfica de Carreras
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'grafic.php', true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        // Insertar la respuesta en el contenedor de la gráfica
                        document.getElementById('grafica-container').innerHTML = xhr.responseText;
                    }
                };
                xhr.send();
                break;
                // Agregar más casos según los otros filtros
            default:
                // Limpiar las gráficas si no se selecciona ninguna opción
                var ctxDefault = document.getElementById('graficaPastel').getContext('2d');
                ctxDefault.clearRect(0, 0, ctxDefault.canvas.width, ctxDefault.canvas.height);
                break;
        }
    }
    </script>

    <!-- Contenedor de la gráfica de Hombres y Mujeres con tamaño personalizado -->
    <div class="grafica-container" style="width: 20%; height: 20%; margin: 0">
        <canvas id="graficaPastel"></canvas>
    </div>

    <div id="carrera" class="grafica-container" style="width: 20%; height: 20%;">
        <canvas id="graficaPastelCarreras"></canvas>
    </div>


    <!-- ... (código posterior) ... -->
    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="scripts.js"></script>
    <script>

    </script>

    <!-- ... (código anterior) ... -->

    <!-- Contenedor de menú desplegable y botón -->
    <div class="sidebar-options-container">
        <!-- Menú desplegable -->
        <div style="display: inline-block; margin-right: 1rem;">
            <label for="opcionesFiltro" class="label-option">Seleccionar filtro:</label>
            <select id="opcionesFiltro" class="dropdown-option">
                <option value="opcion1">Hombres y mujeres</option>
                <option value="opcion2">Carreras</option>
                <option value="opcion3">Más de veintidós años</option>
                <option value="opcion4">Menos de veinte años</option>
                <option value="opcion5">Estatus</option>
                <option value="opcion6">Residencia en Edomex</option>
            </select>
        </div>

        <!-- Botón "Mostrar" centrado debajo del menú desplegable -->
        <div class="centered-button">
            <button class="btn-agregar-alumnos" onclick="mostrarSeleccion()">Mostrar</button>
        </div>
    </div>

    <!-- Contenedor para la gráfica -->
    <div id="grafica-container" style="width: 80%; margin: auto;">
        <canvas id="graficaPastel"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    </script>



    </script>



    <footer>
        <div class="footer-text-left">® Programa Delfín</div>
        <div class="footer-text-right">
            Programa Interinstitucional para el Fortalecimiento de la Investigación y el Posgrado del Pacífico
        </div>
    </footer>

    <!-- Enlace al nuevo archivo JavaScript -->
    <script src="../Js/responsive.js"></script>
    <script src="https://www.gstatic.com/charts/loader.js"></script>


</body>

</html>