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

// Obtener la cantidad de alumnos masculinos y femeninos
$stmtGraficaSexo = $conexion->prepare("SELECT Sexo, COUNT(*) AS cantidad FROM Alumnos GROUP BY Sexo");
$stmtGraficaSexo->execute();
$stmtGraficaSexo->bind_result($sexo, $cantidadAlumnosSexo);

// Inicializar variables para contar hombres y mujeres
$cantidadHombres = 0;
$cantidadMujeres = 0;

while ($stmtGraficaSexo->fetch()) {
    if ($sexo === 'Masculino') {
        $cantidadHombres = $cantidadAlumnosSexo;
    } elseif ($sexo === 'Femenino') {
        $cantidadMujeres = $cantidadAlumnosSexo;
    }
}

$stmtGraficaSexo->close();

// Obtener la cantidad de alumnos de Delfín, Pila y Movilidad
$stmtGraficaPrograma = $conexion->prepare("SELECT Tipo_Programa, COUNT(*) AS cantidad FROM Estancia GROUP BY Tipo_Programa");
$stmtGraficaPrograma->execute();
$stmtGraficaPrograma->bind_result($tipoPrograma, $cantidadAlumnosPrograma);

// Inicializar variables para contar alumnos de Delfín, Pila y Movilidad
$cantidadDelfin = 0;
$cantidadPila = 0;
$cantidadMovilidad = 0;

while ($stmtGraficaPrograma->fetch()) {
    if ($tipoPrograma === 'Delfín') {
        $cantidadDelfin = $cantidadAlumnosPrograma;
    } elseif ($tipoPrograma === 'Pila') {
        $cantidadPila = $cantidadAlumnosPrograma;
    } elseif ($tipoPrograma === 'Movilidad') {
        $cantidadMovilidad = $cantidadAlumnosPrograma;
    }
}

$stmtGraficaPrograma->close();

// Obtener la cantidad de alumnos por carrera
$stmtGraficaCarrera = $conexion->prepare("SELECT Ingenieria, COUNT(*) AS cantidad FROM Alumnos GROUP BY Ingenieria");
$stmtGraficaCarrera->execute();
$stmtGraficaCarrera->bind_result($ingenieria, $cantidadAlumnosIngenieria);

// Crear arrays para almacenar datos de la gráfica de carreras
$labelsCarrera = [];
$dataCarrera = [];

while ($stmtGraficaCarrera->fetch()) {
    $labelsCarrera[] = $ingenieria;
    $dataCarrera[] = $cantidadAlumnosIngenieria;
}

$stmtGraficaCarrera->close();

// Obtener la cantidad de alumnos por municipio
$stmtGraficaMunicipio = $conexion->prepare("SELECT Municipio, COUNT(*) AS cantidad FROM Alumnos GROUP BY Municipio");
$stmtGraficaMunicipio->execute();
$stmtGraficaMunicipio->bind_result($municipio, $cantidadAlumnosMunicipio);

// Crear arrays para almacenar datos de la gráfica de municipios
$labelsMunicipio = [];
$dataMunicipio = [];

while ($stmtGraficaMunicipio->fetch()) {
    $labelsMunicipio[] = $municipio;
    $dataMunicipio[] = $cantidadAlumnosMunicipio;
}

$stmtGraficaMunicipio->close();

// Obtener la cantidad de alumnos por nombre de universidad
$stmtGraficaUniversidad = $conexion->prepare("SELECT Nombre_Universidad, COUNT(*) AS cantidad FROM UniversidadEstancia GROUP BY Nombre_Universidad");
$stmtGraficaUniversidad->execute();
$stmtGraficaUniversidad->bind_result($nombreUniversidad, $cantidadAlumnosUniversidad);

// Crear arrays para almacenar datos de la gráfica de universidades
$labelsUniversidad = [];
$dataUniversidad = [];

while ($stmtGraficaUniversidad->fetch()) {
    $labelsUniversidad[] = $nombreUniversidad;
    $dataUniversidad[] = $cantidadAlumnosUniversidad;
}

$stmtGraficaUniversidad->close();


// Total de alumnos (puedes obtenerlo de otra manera, según tu estructura)
$totalAlumnosSexo = $cantidadHombres + $cantidadMujeres;
$totalAlumnosPrograma = $cantidadDelfin + $cantidadPila + $cantidadMovilidad;

// Cierra la conexión después de realizar todas las operaciones
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto&display=swap">
    <link rel="icon" href = "../Assets/grafica.png" type="image/png"> 
    <script src="buscador.js" defer></script>
    <script src="scripts.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.debug.js"></script>



    <title>Gráficas Estadísticas Directivo</title>
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
    <!-- Incluir la biblioteca Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    /* Estilo base para las gráficas */
    canvas {
        width: 100%;
        height: 100%;
    }

    /* Contenedor principal */
    #contenedor {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        gap: 20px;
    }

    /* Estilo para el menú desplegable y el botón */
    #seleccionarGrafica,
    button {
        margin: 10px;
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
                <a href="tablauniversidadDR.php">Archivos</a>
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


    <div id="contenedor">
        <!-- Agrega un botón para mostrar las gráficas -->
        <button class="btn btn-sm btn-agregar-alumnos" onclick="mostrarGrafica()">Mostrar Gráfica</button>
        <!-- Agrega un botón para mostrar las gráficas -->
        <button class="btn btn-sm btn-agregar-alumnos" onclick="imprimirYDescargar()">Imprimir y Descargar</button>
        <br>
        <br>
        <!-- Agrega el menú desplegable antes de las gráficas -->
        <select class="btn btn-sm btn-agregar-alumnos" id="seleccionarGrafica">
            <option value="graficaSexo">Grafica de Sexo</option>
            <option value="graficaPrograma">Grafica de Programa</option>
            <option value="graficaCarrera">Grafica de Carrera</option>
            <option value="graficaMunicipio">Grafica de Municipio</option>
            <option value="graficaUniversidad">Grafica de Universidad</option>
        </select>





        <!-- Crear contenedores para las gráficas -->
        <div style="width:40%;">
            <canvas id="graficaSexo"></canvas>
        </div>
        <div style="width: 40%;">
            <canvas id="graficaPrograma"></canvas>
        </div>
        <div style="width: 40%;">
            <canvas id="graficaCarrera"></canvas>
        </div>
        <div style="width: 40%;">
            <canvas id="graficaMunicipio"></canvas>
        </div>
        <div style="width: 40%;">
            <canvas id="graficaUniversidad"></canvas>
        </div>
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
    <script>
    // Obtener el contexto del lienzo de la gráfica de sexo
    var ctxSexo = document.getElementById('graficaSexo').getContext('2d');

    // Crear datos para la gráfica de sexo
    var dataSexo = {
        labels: ['Masculino', 'Femenino'],
        datasets: [{
            data: [<?php echo $cantidadHombres; ?>, <?php echo $cantidadMujeres; ?>],
            backgroundColor: ['#36A2EB', '#FFCE56']
        }]
    };

    // Configurar opciones de la gráfica de sexo
    var optionsSexo = {
        responsive: true,
        maintainAspectRatio: false
    };

    // Crear la gráfica de pastel de sexo
    var graficaSexo = new Chart(ctxSexo, {
        type: 'pie',
        data: dataSexo,
        options: optionsSexo
    });

    // Obtener el contexto del lienzo de la gráfica de programa
    var ctxPrograma = document.getElementById('graficaPrograma').getContext('2d');

    // Crear datos para la gráfica de programa
    var dataPrograma = {
        labels: ['Delfín', 'Pila', 'Movilidad'],
        datasets: [{
            data: [<?php echo $cantidadDelfin; ?>, <?php echo $cantidadPila; ?>,
                <?php echo $cantidadMovilidad; ?>
            ],
            backgroundColor: ['#36A2EB', '#FFCE56', '#FF6384']
        }]
    };

    // Configurar opciones de la gráfica de programa
    var optionsPrograma = {
        responsive: true,
        maintainAspectRatio: false
    };

    // Crear la gráfica de pastel de programa
    var graficaPrograma = new Chart(ctxPrograma, {
        type: 'pie',
        data: dataPrograma,
        options: optionsPrograma
    });

    // Obtener el contexto del lienzo de la gráfica de carrera
    var ctxCarrera = document.getElementById('graficaCarrera').getContext('2d');

    // Crear datos para la gráfica de carrera
    var dataCarrera = {
        labels: <?php echo json_encode($labelsCarrera); ?>,
        datasets: [{
            data: <?php echo json_encode($dataCarrera); ?>,
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF8C00']
        }]
    };

    // Configurar opciones de la gráfica de carrera
    var optionsCarrera = {
        responsive: true,
        maintainAspectRatio: false
    };

    // Crear la gráfica de pastel de carrera
    var graficaCarrera = new Chart(ctxCarrera, {
        type: 'pie',
        data: dataCarrera,
        options: optionsCarrera
    });

    // Obtener el contexto del lienzo de la gráfica de municipio
    var ctxMunicipio = document.getElementById('graficaMunicipio').getContext('2d');

    // Crear datos para la gráfica de municipio
    var dataMunicipio = {
        labels: <?php echo json_encode($labelsMunicipio); ?>,
        datasets: [{
            data: <?php echo json_encode($dataMunicipio); ?>,
            backgroundColor: getRandomColorArray(<?php echo count($labelsMunicipio); ?>)
        }]
    };

    // Configurar opciones de la gráfica de municipio
    var optionsMunicipio = {
        responsive: true,
        maintainAspectRatio: false
    };

    // Crear la gráfica de pastel de municipio
    var graficaMunicipio = new Chart(ctxMunicipio, {
        type: 'pie',
        data: dataMunicipio,
        options: optionsMunicipio
    });

    // Obtener el contexto del lienzo de la gráfica de universidad
    var ctxUniversidad = document.getElementById('graficaUniversidad').getContext('2d');

    // Crear datos para la gráfica de universidad
    var dataUniversidad = {
        labels: <?php echo json_encode($labelsUniversidad); ?>,
        datasets: [{
            data: <?php echo json_encode($dataUniversidad); ?>,
            backgroundColor: getRandomColorArray(<?php echo count($labelsUniversidad); ?>)
        }]
    };

    // Configurar opciones de la gráfica de universidad
    var optionsUniversidad = {
        responsive: true,
        maintainAspectRatio: false
    };

    // Crear la gráfica de pastel de universidad
    var graficaUniversidad = new Chart(ctxUniversidad, {
        type: 'pie',
        data: dataUniversidad,
        options: optionsUniversidad
    });

    // Función para generar un array de colores aleatorios
    function getRandomColorArray(numColors) {
        var colors = [];
        for (var i = 0; i < numColors; i++) {
            colors.push(getRandomColor());
        }
        return colors;
    }

    // Función para generar un color aleatorio en formato hexadecimal
    function getRandomColor() {
        var letters = '0123456789ABCDEF';
        var color = '#';
        for (var i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }
    </script>
    <script>
    // Agrega una función para mostrar la gráfica seleccionada
    function mostrarGrafica() {
        // Obtener el valor seleccionado del menú desplegable
        var seleccion = document.getElementById('seleccionarGrafica').value;

        // Ocultar todas las gráficas
        document.getElementById('graficaSexo').style.display = 'none';
        document.getElementById('graficaPrograma').style.display = 'none';
        document.getElementById('graficaCarrera').style.display = 'none';
        document.getElementById('graficaMunicipio').style.display = 'none';
        document.getElementById('graficaUniversidad').style.display = 'none';

        // Mostrar la gráfica seleccionada
        document.getElementById(seleccion).style.display = 'block';
    }
    </script>

    <script>
    // Agrega una función para imprimir y descargar la gráfica
    function imprimirYDescargar() {
        // Obtener el valor seleccionado del menú desplegable
        var seleccion = document.getElementById('seleccionarGrafica').value;

        // Mostrar la gráfica seleccionada
        document.getElementById(seleccion).style.display = 'block';

        // Obtener el contexto de la gráfica seleccionada
        var ctx = document.getElementById(seleccion).getContext('2d');

        // Crear un objeto de la clase Image de JavaScript
        var img = new Image();

        // Convertir el lienzo de la gráfica en una imagen
        img.src = ctx.canvas.toDataURL('image/png');

        // Crear un objeto de la clase jsPDF con orientación horizontal
        var pdf = new jsPDF('landscape');

        // Agregar la imagen al documento PDF
        pdf.addImage(img, 'PNG', 10, 10);

        // Agregar nueva página para los datos adicionales según la gráfica
        if (seleccion === 'graficaSexo') {
            pdf.addPage();
            pdf.text('Datos de la Gráfica de Sexo', 10, 10);
            pdf.text('Hombres: <?php echo $cantidadHombres; ?>', 10, 20);
            pdf.text('Mujeres: <?php echo $cantidadMujeres; ?>', 10, 30);
        } else if (seleccion === 'graficaPrograma') {
            pdf.addPage();
            pdf.text('Datos de la Gráfica de Programa', 10, 10);
            pdf.text('Delfín: <?php echo $cantidadDelfin; ?>', 10, 20);
            pdf.text('Pila: <?php echo $cantidadPila; ?>', 10, 30);
            pdf.text('Movilidad: <?php echo $cantidadMovilidad; ?>', 10, 40);
        } else if (seleccion === 'graficaCarrera') {
            pdf.addPage();
            pdf.text('Datos de la Gráfica de Carrera', 10, 10);
            <?php
        // Agrega la lógica para obtener y mostrar los datos de la gráfica de Carrera
        // Personaliza según tus necesidades
        foreach ($labelsCarrera as $index => $label) {
            $cantidadAlumnos = $dataCarrera[$index];
            echo "pdf.text('{$label}: {$cantidadAlumnos}', 10, " . (20 + $index * 10) . ");";
        }
        ?>
        } else if (seleccion === 'graficaUniversidad') {
            pdf.addPage();
            pdf.text('Datos de la Gráfica de Universidad', 10, 10);
            <?php
        // Agrega la lógica para obtener y mostrar los datos de la gráfica de Universidad
        // Personaliza según tus necesidades
        foreach ($labelsUniversidad as $index => $label) {
            $cantidadAlumnos = $dataUniversidad[$index];
            echo "pdf.text('{$label}: {$cantidadAlumnos}', 10, " . (20 + $index * 10) . ");";
        }
        ?>
        }

        // Descargar el documento PDF automáticamente
        pdf.save('grafica.pdf');
    }
    </script>

</body>

</html>