<?php
session_start();
include('../DB/conexion2.php');

function validarFormulario() {
    global $conexion;

    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    $consulta = $conexion->prepare("SELECT id, type FROM administradores WHERE user = ? AND pass = ?");
    $consulta->bind_param("ss", $usuario, $contrasena);

    $consulta->execute();
    $consulta->store_result();
    
    $consulta->bind_result($id, $tipoUsuario);
    $consulta->fetch();

    $consulta->close();
    if (!empty($id)) {
        // Almacena el nombre de usuario en la sesión
        $_SESSION['usuario'] = $usuario;
    
        if ($tipoUsuario == "Coordinador de movilidad") {
            header("Location: tablauniversidad.php");
            exit();
        } elseif ($tipoUsuario == "Directivo") {
            header("Location: tablauniversidadDR.php");
            exit();
        } elseif ($tipoUsuario == "Jefe de carrera plasticos") {
            header("Location: tablauniversidadPL.php");
            exit();
        } elseif ($tipoUsuario == "Jefe de carrera proceso de produccion") {
            header("Location: tablauniversidadP.php");
            exit();
        } elseif ($tipoUsuario == "Jefe de carrera seguridad ciudadana") {
            header("Location: tablauniversidadC.php");
            exit();
        } elseif ($tipoUsuario == "Jefe de carrera Software") {
            header("Location: tablauniversidadS.php");
            exit();
        }
    }
    

    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include('../DB/conexion2.php');

    if (empty($_POST['usuario']) || empty($_POST['contrasena'])) {
        $error = "Error: Favor de rellenar todos los campos.";
    } elseif (validarFormulario()) {
        // La redirección ya está manejada en validarFormulario()
    } else {
        $error = "Error: Usuario o contraseña incorrectos, comuniquese con coordinador general para obtener sus datos";
    }

    $conexion->close();
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto&display=swap">
    <link rel="icon" href = "../Assets/user.png" type="image/png"> 
    <title>Inicio de sesión</title>
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
            </div>
        </div>

        <!-- Contenido de la página -->

    </main>

    <!-- Sección de Recuperar Datos de Sesión -->
    <div class="sidebar-section right-section">
        <h2 class="login-heading" style="color: #F58221;">Inicio de sesión</h2>
        <div class="login-box">
            <?php
            if (isset($error)) {
                echo "<p style='color: red;'>$error</p>";
            }
            ?>
            <form class="login-form" action="" method="post">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario">

                <label for="contrasena">Contraseña:</label>
                <input type="password" id="contrasena" name="contrasena">

                <div class="button-container">
                    <button type="submit" class="login-button">Ingresar</button>
                </div>
            </form>
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
</body>

</html>