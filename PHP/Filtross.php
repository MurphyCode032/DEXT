<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto&display=swap">
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
                <a href="filtros.php">Filtros</a>
            </div>
            <!-- Información del usuario -->
            <div class="user-info" style="display: flex; align-items: center; margin-right: 2rem;">
                <img src="../Assets/user.png" alt="Usuario" class="user-icon" style="width: 50px; margin-right: 10px;"
                    onclick="confirmarRedireccion()">
                <span class="user-name" style="color: #F58221;"><?php echo $usuario; ?></span>
            </div>
        </div>
        </main>



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