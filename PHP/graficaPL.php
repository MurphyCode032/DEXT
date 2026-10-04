<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: Index.php");
    exit();
}
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


    // Consulta para obtener la cantidad de alumnos hombres y mujeres de SC
    $resultadoSexo = $conexion->query("SELECT Sexo, COUNT(*) as Cantidad FROM Alumnos WHERE Ingenieria IN ('Ing. en pl?sticos', 'Ingeniería en plásticos', 'Ing. en plásticos') GROUP BY Sexo");

    // Arreglos para almacenar los datos de la primera gráfica
    $labels = [];
    $data = [];

    // Verificar si la consulta fue exitosa antes de continuar
    if ($resultadoSexo) {
        while ($filaSexo = $resultadoSexo->fetch_assoc()) {
            $labels[] = $filaSexo['Sexo'];
            $data[] = $filaSexo['Cantidad'];
        }
    } else {
        echo "Error en la consulta de la primera gráfica: " . $conexion->error;
    }

    // Consulta para obtener el Tipo_Programa de la tabla estancia solo para SC
    $resultadoTipoPrograma = $conexion->query("SELECT Tipo_Programa, COUNT(*) as Cantidad FROM estancia e 
                                           JOIN alumnos a ON e.Alumno_id = a.id
                                           WHERE a.Ingenieria IN ('Ing. en pl?sticos', 'Ingeniería en plásticos', 'Ing. en plásticos') 
                                           GROUP BY Tipo_Programa");

    // Arreglos para almacenar los datos de la segunda gráfica
    $labelsTipoPrograma = [];
    $dataTipoPrograma = [];

    // Verificar si la consulta fue exitosa antes de continuar
    if ($resultadoTipoPrograma) {
        while ($filaTipoPrograma = $resultadoTipoPrograma->fetch_assoc()) {
            $labelsTipoPrograma[] = $filaTipoPrograma['Tipo_Programa'];
            $dataTipoPrograma[] = $filaTipoPrograma['Cantidad'];
        }
    } else {
        echo "Error en la consulta de la segunda gráfica: " . $conexion->error;
    }

    // Consulta para obtener la cantidad de alumnos en SC
    $resultadoIngenieria = $conexion->query("SELECT COUNT(*) as Cantidad FROM Alumnos WHERE Ingenieria IN ('Ing. en pl?sticos', 'Ingeniería en plásticos', 'Ing. en plásticos')");

    // Variable para almacenar el dato de la tercera gráfica
    $cantidadIngenieria = 0;

    // Verificar si la consulta fue exitosa antes de continuar
    if ($resultadoIngenieria) {
        $filaIngenieria = $resultadoIngenieria->fetch_assoc();
        $cantidadIngenieria = $filaIngenieria['Cantidad'];
    } else {
        echo "Error en la consulta de la tercera gráfica: " . $conexion->error;
    }

    // Consulta para obtener la cantidad de alumnos por nivel de inglés en SC
    $resultadoNivelIdioma = $conexion->query("SELECT e.Nivel_Idioma, COUNT(*) as Cantidad 
                                              FROM estancia e
                                              JOIN alumnos a ON e.Alumno_id = a.id
                                              WHERE a.Ingenieria IN ('Ing. en pl?sticos', 'Ingeniería en plásticos', 'Ing. en plásticos') 
                                              GROUP BY e.Nivel_Idioma");

    // Arreglos para almacenar los datos de la cuarta gráfica
    $labelsNivelIdioma = [];
    $dataNivelIdioma = [];

    // Verificar si la consulta fue exitosa antes de continuar
    if ($resultadoNivelIdioma) {
        while ($filaNivelIdioma = $resultadoNivelIdioma->fetch_assoc()) {
            $labelsNivelIdioma[] = $filaNivelIdioma['Nivel_Idioma'];
            $dataNivelIdioma[] = $filaNivelIdioma['Cantidad'];
        }
    } else {
        echo "Error en la consulta de la cuarta gráfica: " . $conexion->error;
    }

        // Consulta para obtener la cantidad de alumnos por certificación en SC
        $resultadoCertificacion = $conexion->query("SELECT Documento_aval, COUNT(*) as Cantidad 
        FROM estancia e
        JOIN alumnos a ON e.Alumno_id = a.id
        WHERE a.Ingenieria IN ('Ing. en pl?sticos', 'Ingeniería en plásticos', 'Ing. en plásticos') 
        GROUP BY Documento_aval");

        // Arreglos para almacenar los datos de la quinta gráfica
        $labelsCertificacion = [];
        $dataCertificacion = [];

        // Verificar si la consulta fue exitosa antes de continuar
        if ($resultadoCertificacion) {
        while ($filaCertificacion = $resultadoCertificacion->fetch_assoc()) {
        $labelsCertificacion[] = $filaCertificacion['Documento_aval'];
        $dataCertificacion[] = $filaCertificacion['Cantidad'];
        }
        } else {
        echo "Error en la consulta de la quinta gráfica: " . $conexion->error;
        }


    // Convertir a formato JSON para pasar a JavaScript
    $labelsJSON = json_encode($labels);
    $dataJSON = json_encode($data);
    $labelsTipoProgramaJSON = json_encode($labelsTipoPrograma);
    $dataTipoProgramaJSON = json_encode($dataTipoPrograma);
    $labelsNivelIdiomaJSON = json_encode($labelsNivelIdioma);
    $dataNivelIdiomaJSON = json_encode($dataNivelIdioma);
    $labelsCertificacionJSON = json_encode($labelsCertificacion);
    $dataCertificacionJSON = json_encode($dataCertificacion);
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.debug.js"></script>

    <script src="https://rawgit.com/eKoopmans/html2pdf/master/dist/html2pdf.bundle.js"></script>

    <title>Graficas Ingeniería en Plasticos</title>
    <style>
    /* Estilo base para las gráficas */
    canvas {
        width: 250px;
        height: 250px;
    }

    /* Clase para ocultar las gráficas */
    .ocultar {
        display: none;
    }
    </style>
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
                <a href="tablauniversidad.php">Archivos</a>
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


    <!-- Agrega un menú desplegable y dos botones -->
    <!-- Div que contiene el párrafo centrado -->
    <div class="centrado">
        <p>Seleccione una gráfica y muestrela en pantalla para poder imprimirla.</p>
    </div>
    <div style="width: 80%; margin: auto;">
        <label for="graficaSelector">Selecciona una gráfica:</label>
        <select class="btn btn-sm btn-agregar-alumnos" id="graficaSelector">
            <option value="graficaAlumnosCanvas">Alumnos por Género</option>
            <option value="graficaTipoProgramaCanvas">Tipo de Programa</option>
            <option value="graficaIngenieriaCanvas">Ingeniería en plasticos</option>
            <option value="graficaNivelIdiomaCanvas">Nivel de Idioma</option>
            <option value="graficaCertificacionCanvas">Certificación</option>
        </select>
        <button class="btn btn-sm btn-agregar-alumnos" onclick="mostrarGrafica()">Mostrar gráfica</button>
        <button class="btn btn-sm btn-agregar-alumnos" onclick="imprimirGrafica()">Imprimir gráfica</button>
    </div>

    <!-- Contenedor para la primera gráfica -->
    <div style="width: 45%; margin: auto;">
        <canvas id="graficaAlumnosCanvas" width="100" height="50"></canvas>
    </div>

    <!-- Contenedor para la segunda gráfica -->
    <div style="width: 45%; margin: auto;">
        <canvas id="graficaTipoProgramaCanvas" width="200" height="100"></canvas>
    </div>

    <!-- Contenedor para la tercera gráfica -->
    <div style="width: 45%; margin: auto;">
        <canvas id="graficaIngenieriaCanvas" width="200" height="100"></canvas>
    </div>

    <!-- Contenedor para la cuarta gráfica -->
    <div style="width: 45%; margin: auto;">
        <canvas id="graficaNivelIdiomaCanvas" width="200" height="100"></canvas>
    </div>

    <!-- Contenedor para la quinta gráfica -->
    <div style="width: 45%; margin: auto;">
        <canvas id="graficaCertificacionCanvas" width="200" height="100"></canvas>
    </div>

    <br>
    <br>

    <footer>
        <div class="footer-text-left">® Programa Delfín</div>
        <div class="footer-text-right">
            Programa Interinstitucional para el Fortalecimiento de la Investigación y el Posgrado del Pacífico
        </div>
    </footer>
    <!-- Script para inicializar y mostrar las gráficas -->
    <script>
    function mostrarGrafica() {
        var selector = document.getElementById('graficaSelector');
        var graficaSeleccionada = selector.value;

        // Oculta todas las gráficas
        document.getElementById('graficaAlumnosCanvas').style.display = 'none';
        document.getElementById('graficaTipoProgramaCanvas').style.display = 'none';
        document.getElementById('graficaIngenieriaCanvas').style.display = 'none';
        document.getElementById('graficaNivelIdiomaCanvas').style.display = 'none';
        document.getElementById('graficaCertificacionCanvas').style.display = 'none';

        // Muestra solo la gráfica seleccionada
        document.getElementById(graficaSeleccionada).style.display = 'block';

        // Configurar y mostrar la gráfica seleccionada
        switch (graficaSeleccionada) {
            case 'graficaAlumnosCanvas':
                // Configurar y mostrar la primera gráfica
                var ctx1 = document.getElementById('graficaAlumnosCanvas').getContext('2d');
                var graficaAlumnos = new Chart(ctx1, {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo $labelsJSON; ?>,
                        datasets: [{
                            data: <?php echo $dataJSON; ?>,
                            backgroundColor: ['blue', 'pink'],
                        }]
                    }
                });
                break;
            case 'graficaTipoProgramaCanvas':
                // Configurar y mostrar la segunda gráfica
                var ctx2 = document.getElementById('graficaTipoProgramaCanvas').getContext('2d');
                var graficaTipoPrograma = new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo $labelsTipoProgramaJSON; ?>,
                        datasets: [{
                            label: 'Cantidad',
                            data: <?php echo $dataTipoProgramaJSON; ?>,
                            backgroundColor: ['orange', 'blue', 'pink'], // Cambiar los colores aquí
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
                break;

            case 'graficaIngenieriaCanvas':
                // Configurar y mostrar la tercera gráfica
                var ctx3 = document.getElementById('graficaIngenieriaCanvas').getContext('2d');
                var graficaIngenieria = new Chart(ctx3, {
                    type: 'bar',
                    data: {
                        labels: ['Ing. en pl?sticos', 'Ingeniería en plásticos', 'Ing. en plásticos'],
                        datasets: [{
                            data: [<?php echo $cantidadIngenieria; ?>],
                            backgroundColor: ['lightgreen'],
                        }]
                    }
                });
                break;
            case 'graficaNivelIdiomaCanvas':
                // Configurar y mostrar la cuarta gráfica
                var ctx4 = document.getElementById('graficaNivelIdiomaCanvas').getContext('2d');
                var graficaNivelIdioma = new Chart(ctx4, {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo $labelsNivelIdiomaJSON; ?>,
                        datasets: [{
                            label: 'Cantidad',
                            data: <?php echo $dataNivelIdiomaJSON; ?>,
                            backgroundColor: ['orange', 'blue', 'pink'],
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
                break;
            case 'graficaCertificacionCanvas':
                // Configurar y mostrar la quinta gráfica
                var ctx5 = document.getElementById('graficaCertificacionCanvas').getContext('2d');
                var graficaCertificacion = new Chart(ctx5, {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo $labelsCertificacionJSON; ?>,
                        datasets: [{
                            label: 'Cantidad',
                            data: <?php echo $dataCertificacionJSON; ?>,
                            backgroundColor: 'blue',
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
                break;
            default:
                break;
        }
    }

    function imprimirGrafica() {
        var selector = document.getElementById('graficaSelector');
        var graficaSeleccionada = selector.value;

        // Obtener el canvas de la gráfica seleccionada
        var canvas = document.getElementById(graficaSeleccionada);

        // Crear un objeto html2pdf con el canvas y configurar el formato como horizontal
        var pdf = new html2pdf(canvas, {
            margin: 10,
            filename: 'grafica.pdf',
            image: {
                type: 'jpeg',
                quality: 0.98
            },
            html2canvas: {
                scale: 2
            },
            jsPDF: {
                unit: 'mm',
                format: 'a4',
                orientation: 'landscape'
            } // Configurar la orientación como 'landscape'
        });

        // Descargar el archivo PDF con un nombre específico (puedes personalizar el nombre)
        pdf.save();
    }
    </script>

    <!-- Enlace al nuevo archivo JavaScript -->
    <script src="../Js/responsive.js"></script>

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
    </script>


</body>

</html>