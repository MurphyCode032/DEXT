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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['subir-individual'])) {
        $nombre = $_POST['nombre'];
        $sexo = $_POST['sexo'];
        $numControl = $_POST['num_control'];
        $fechaNacimiento = $_POST['fecha_nacimiento'];
        $correo = $_POST['correo'];
        $correoP = $_POST['Correo_Personal'];
        $telefono = $_POST['telefono'];
        $ingenieria = $_POST['ingenieria'];
        $estado = $_POST['estado'];
        $municipio = $_POST['municipio'];

        // Validar el formato del Número de Control
        $expresionRegular = "/^[A-Z]?[0-9]{8,9}$/";
        if (!preg_match($expresionRegular, $numControl)) {
            $mensaje = "Error: El formato del Número de Control no es válido. Debe comenzar con una letra (opcional) seguida de 8 o 9 dígitos.";
        } else {
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
                    $nombreUniversidad = $_POST['nombre_universidad'];
                    $paisUniversidad = $_POST['pais'];
                    $coordinadorUniversidad = $_POST['coordinador'];
                    $telefonoCoordinadorUniversidad = $_POST['telefono_coordinador'];
                    $correoCoordinadorUniversidad = $_POST['correo_coordinador'];
                    $nombreProyectoInvestigacion = $_POST['nombre_proyecto']; // opcional
                    $observacionesUniversidad = $_POST['observaciones']; // opcional

                    $stmtUniversidad = $conexion->prepare("INSERT INTO UniversidadEstancia (Alumno_id, Nombre_Universidad, Pais, Coordinador, Telefono_Coordinador, Correo_Coordinador, Nombre_Proyecto_Investigacion, Observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

                    if ($stmtUniversidad === false) {
                        die("Error en la preparación de la consulta para UniversidadEstancia: " . $conexion->error);
                    }

                    $stmtUniversidad->bind_param("isssssss", $idAlumno, $nombreUniversidad, $paisUniversidad, $coordinadorUniversidad, $telefonoCoordinadorUniversidad, $correoCoordinadorUniversidad, $nombreProyectoInvestigacion, $observacionesUniversidad);
                    $stmtUniversidad->execute();

                    if ($stmtUniversidad->affected_rows > 0) {
                        // Modificación para la inserción en la tabla Estancia
                        $tipoPrograma = $_POST['tipo_programa'];
                        $fechaRegistro = $_POST['fecha_registro'];
                        $periodo = $_POST['periodo'];
                        $promedio = $_POST['promedio'];
                        $semestre = $_POST['semestre'];
                        $idioma = $_POST['idioma'];
                        $nivelIdioma = $_POST['nivel_idioma'];
                        $documentoAval = $_POST['documento_aval'];
                        $fechaExpiracion = $_POST['fecha_expiracion'];

                        // Continuar con la inserción en la tabla Estancia
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
    }
}

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
    <link rel="icon" href="../Assets/alumno.png" type="image/png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto&display=swap">
    <title>Agregar alumno</title>

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
                <a href="tablauniversidad.php">Archivos</a>

                <!--  <a href="alumno.php">Agregar alumno</a> -->
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
    <div class="formulario">
        <form method="POST" action="alumno.php">
            <div class="form-group">
                <label for="nombre">Nombre completo:</label>
                <input type="text" name="nombre" required>
            </div>


            <div class="form-group">
                <label for="sexo">Sexo:</label>
                <select class="btn-agregar-alumnos" name="sexo" required>
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
                </select>
            </div>

            <div class="form-group">
                <label for="num_control">Número de control:</label>
                <input type="text" name="num_control" required>
            </div>

            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de nacimiento:</label>
                <input type="text" placeholder="aaaa/mm/dd" name="fecha_nacimiento" required
                    onblur="validarFormatoFecha(this)">
            </div>

            <div class="form-group">
                <label for="correo">Correo institucional:</label>
                <input type="email" name="correo" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" required>
            </div>

            <div class="form-group">
                <label for="correo">Correo Personal:</label>
                <input type="email" name="Correo_Personal" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                    required>
            </div>

            <div class="form-group">
                <label for="telefono">Teléfono celular:</label>
                <input type="tel" name="telefono" pattern="[0-9]{10,12}" required>
            </div>

            <div class="form-group">
                <label for="ingenieria">Ingeniería:</label>
                <select class="btn-agregar-alumnos" name="ingenieria" required>
                    <option value="" disabled selected>Seleccione su carrera</option>
                    <option value="Ing. en plasticos">Ing. en plásticos</option>
                    <option value="Ing. En procesode producción">Ing. en proceso de producción</option>
                    <option value="Ing. En software">Ing. en software</option>
                    <option value="Lic. En seguridad ciudana">Lic. en seguridad ciudadana</option>
                </select>
            </div>

            <div class="form-group">
                <label for="estado">Estado:</label>
                <select class="btn-agregar-alumnos" name="estado" required>
                    <option value="" disabled selected>Selecciona un estado</option>
                    <option value="Aguascalientes">Aguascalientes</option>
                    <option value="Baja California">Baja California</option>
                    <option value="Baja California Sur">Baja California Sur</option>
                    <option value="Campeche">Campeche</option>
                    <option value="Chiapas">Chiapas</option>
                    <option value="Chihuahua">Chihuahua</option>
                    <option value="Coahuila">Coahuila</option>
                    <option value="Colima">Colima</option>
                    <option value="Durango">Durango</option>
                    <option value="Guanajuato">Guanajuato</option>
                    <option value="Guerrero">Guerrero</option>
                    <option value="Hidalgo">Hidalgo</option>
                    <option value="Jalisco">Jalisco</option>
                    <option value="Estado de México">Estado de México</option>
                    <option value="Michoacán">Michoacán</option>
                    <option value="Morelos">Morelos</option>
                    <option value="Nayarit">Nayarit</option>
                    <option value="Nuevo León">Nuevo León</option>
                    <option value="Oaxaca">Oaxaca</option>
                    <option value="Puebla">Puebla</option>
                    <option value="Querétaro">Querétaro</option>
                    <option value="Quintana Roo">Quintana Roo</option>
                    <option value="San Luis Potosí">San Luis Potosí</option>
                    <option value="Sinaloa">Sinaloa</option>
                    <option value="Sonora">Sonora</option>
                    <option value="Tabasco">Tabasco</option>
                    <option value="Tamaulipas">Tamaulipas</option>
                    <option value="Tlaxcala">Tlaxcala</option>
                    <option value="Veracruz">Veracruz</option>
                    <option value="Yucatán">Yucatán</option>
                    <option value="Zacatecas">Zacatecas</option>
                </select>
            </div>


            <div class="form-group">
                <label for="municipio">Municipio:</label>
                <input type="text" name="municipio" required>
            </div>





            <!-- Datos de la universidad de estancia -->

            <div class="form-group">
                <label for="nombre_universidad">Nombre de la universidad:</label>
                <input type="text" name="nombre_universidad" required>
            </div>

            <div class="form-group">
                <label for="pais">País:</label>
                <input type="text" name="pais" required>
            </div>

            <div class="form-group">
                <label for="coordinador">Nombre completo del coordinador:</label>
                <input type="text" name="coordinador" required>
            </div>

            <div class="form-group">
                <label for="telefono_coordinador">Teléfono del coordinador:</label>
                <input type="tel" name="telefono_coordinador" pattern="[0-9]{10,12}" required>
            </div>

            <div class="form-group">
                <label for="correo_coordinador">Correo del coordinador:</label>
                <input type="email" name="correo_coordinador" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                    required>
            </div>
            <div class="form-group">
                <label for="nombre_proyecto">Nombre del proyecto de investigación (opcional):</label>
                <input type="text" name="nombre_proyecto" optional>
            </div>

            <div class="form-group">
                <label for="observaciones">Observaciones (opcional):</label>
                <textarea name="observaciones" rows="4" cols="50" style="max-width: 100%; max-height: 100px;"
                    optional></textarea>
            </div>


            <!-- Datos de la estancia -->

            <div class="form-group">
                <label for="tipo_programa">Tipo de programa:</label>
                <select class="btn-agregar-alumnos" name="tipo_programa" required>
                    <option value="Delfín">Delfín</option>
                    <option value="Movilidad">Movilidad</option>
                    <option value="Pila">Pila</option>
                </select>
            </div>

            <div class="form-group">
                <label for="fecha_registro">Fecha de registro:</label>
                <input type="text" placeholder="aaaa/mm/dd" name="fecha_registro" required
                    onblur="validarFormatoFecha(this)">
            </div>

            <div class="form-group">
                <label for="periodo">Periodo:</label>
                <select class="btn-agregar-alumnos" name="periodo" required>
                    <option value="Verano">Verano</option>
                    <option value="Enero-junio">Enero-junio</option>
                    <option value="Agosto-diciembre">Agosto-diciembre</option>
                </select>
            </div>

            <div class="form-group">
                <label for="promedio">Promedio:</label>
                <input type="number" name="promedio" min="80" max="100" required>
            </div>

            <div class="form-group">
                <label for="semestre">Semestre:</label>
                <input type="number" name="semestre" min="4" max="13" required>
            </div>

            <div class="form-group">
                <label for="idioma">Idioma:</label>
                <select name="idioma" required>
                    <option value="espanol">Español</option>
                    <option value="ingles">Inglés</option>
                    <option value="frances">Francés</option>
                    <option value="aleman">Alemán</option>
                    <option value="italiano">Italiano</option>
                    <option value="portugues">Portugués</option>
                    <option value="chino">Chino</option>
                    <option value="japones">Japonés</option>
                    <option value="arabe">Árabe</option>
                    <option value="ruso">Ruso</option>
                </select>
            </div>


            <div class="form-group">
                <label for="nivel_idioma">Nivel de idioma:</label>
                <select class="btn-agregar-alumnos" name="nivel_idioma" required>
                    <option value="B2">B2</option>
                    <option value="C1">C1</option>
                    <option value="C2">C2</option>
                </select>
            </div>


            <div class="form-group">
                <label for="documento_aval">Documento que avala:</label>
                <input type="text" name="documento_aval" required>
            </div>

            <div class="form-group">
                <label for="fecha_expiracion">Fecha de expiración:</label>
                <input type="text" placeholder="aaaa/mm/dd" name="fecha_expiracion" required
                    onblur="validarFormatoFecha(this)">
            </div>

            <div class="botones">
                <button class="btn-agregar-alumnos" type="submit" name="subir-individual">Agregar individualmente
                </button>
            </div>
        </form>
        <p id="mensaje"><?php echo $mensaje; ?></p>
    </div>
    <br>
    <br>
    </div>
    <footer>
        <div class="footer-text-left">® Programa Delfín</div>
        <div class="footer-text-right">
            Programa Interinstitucional para el Fortalecimiento de la Investigación y el Posgrado del Pacífico
        </div>
    </footer>

    <!-- Enlace al nuevo archivo JavaScript -->
    <script src="../Js/responsive.js"></script>
    <script src="ruta/del/proyecto/buscador.js"></script>

    <script>
    // Función para validar el formato de fecha (aaaa/mm/dd)
    function validarFormatoFecha(input) {
        const regexFecha = /^\d{4}\/\d{2}\/\d{2}$/;

        if (!regexFecha.test(input.value)) {
            alert('Error: El formato de fecha debe ser aaaa/mm/dd.');
            // Limpiar el campo de fecha
            input.value = '';
        }
    }
    </script>


</body>

</html>