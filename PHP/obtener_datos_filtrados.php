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

$conexion = new mysqli($host, $usuarioBD, $contrasenaBD, $nombreBD);

if ($conexion->connect_error) {
    die("La conexión falló: " . $conexion->connect_error);
}

// Función para construir la parte del WHERE de la consulta SQL
function construirFiltroSQL($estadosSeleccionados) {
    $filtros = array();

    if ($estadosSeleccionados['Hombres']) {
        $filtros[] = "Sexo = 'Hombres'";
    }

    // Agregar más condiciones según las opciones que tengas

    if (!empty($filtros)) {
        return "WHERE " . implode(" AND ", $filtros);
    }

    return "";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $datosJson = file_get_contents("php://input");
    $estadosSeleccionados = json_decode($datosJson, true);

    // Construir la parte del WHERE de la consulta SQL
    $filtroSQL = construirFiltroSQL($estadosSeleccionados);

    // Consultar datos filtrados
    $query = "SELECT * FROM alumnos $filtroSQL";
    $result = $conexion->query($query);

    $datos = array();
    while ($row = $result->fetch_assoc()) {
        $datos[] = $row;
    }

    // Puedes devolver los datos como JSON o realizar otras acciones aquí
    echo json_encode($datos);
} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Obtener datos de hombres de la base de datos
    // Debes completar esta parte según la estructura de tu base de datos

    // Realizar la consulta SQL
    $query = "SELECT COUNT(*) as cantidadHombres FROM alumnos WHERE Sexo = 'Hombres'";
    $result = $conexion->query($query);

    // Obtener el resultado
    $datos = $result->fetch_assoc();

    // Devolver los datos como JSON
    echo json_encode($datos);
}

function mostrarGraficaCirculo(datos) {
    try {
        // Crear un array de datos en el formato esperado por Google Charts
        const data = new google.visualization.DataTable();
        data.addColumn('string', 'Sexo');
        data.addColumn('number', 'Cantidad');

        data.addRows([
            ['Hombres', datos.cantidadHombres],
            ['Mujeres', datos.cantidadMujeres]
        ]);

        // Opciones de la gráfica
        const options = {
            title: 'Distribución de Hombres en la Base de Datos',
            pieHole: 0.4,
        };

        // Crear e inicializar la gráfica de círculo
        const chart = new google.visualization.PieChart(document.getElementById('grafica'));
        chart.draw(data, options);
    } catch (error) {
        console.error('Error al mostrar la gráfica:', error);
        mostrarMensaje('Error al mostrar la gráfica.', true);
    }
}

?>
<script src="https://www.gstatic.com/charts/loader.js"></script>
<script>
google.charts.load('current', {
    'packages': ['corechart']
});
google.charts.setOnLoadCallback(function() {
    // Aquí no es necesario hacer nada, solo cargar la biblioteca
});
</script>