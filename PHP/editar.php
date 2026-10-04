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

$idEditar = $_GET['id']; // Obtener el ID de la URL

// Realizar una consulta para obtener la información de las tres tablas
$stmtEditar = $conexion->prepare("SELECT * FROM Alumnos
                                  INNER JOIN UniversidadEstancia ON Alumnos.id = UniversidadEstancia.Alumno_id
                                  INNER JOIN Estancia ON Alumnos.id = Estancia.Alumno_id
                                  WHERE Alumnos.id = ?");
$stmtEditar->bind_param("i", $idEditar);
$stmtEditar->execute();
$resultadoEditar = $stmtEditar->get_result();
$datosEditar = $resultadoEditar->fetch_assoc();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['subir-individual'])) {
        // Obtener el ID del alumno a actualizar
        $idAlumno = $datosEditar['id'];

         // Validar el formato del Número de Control
        $expresionRegular = "/^[A-Z]?[0-9]{8,9}$/";
        $numControl = isset($_POST['num_control']) ? mysqli_real_escape_string($conexion, $_POST['num_control']) : '';

        if (!preg_match($expresionRegular, $numControl)) {
            $mensaje = "Error: El formato del Número de Control no es válido. Debe comenzar con una letra (opcional) seguida de 8 o 9 dígitos.";
        } else {

        // Validaciones y limpieza de datos (similar a tu código existente)
        $numControl = isset($_POST['num_control']) ? mysqli_real_escape_string($conexion, $_POST['num_control']) : '';
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
        $fechaNacimiento = mysqli_real_escape_string($conexion, $_POST['fecha_nacimiento']);
        $sexo = mysqli_real_escape_string($conexion, $_POST['sexo']);
        $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
        $correoP = mysqli_real_escape_string($conexion, $_POST['Correo_Personal']);
        $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
        $ingenieria = mysqli_real_escape_string($conexion, $_POST['ingenieria']);
        $estado = mysqli_real_escape_string($conexion, $_POST['estado']);
        $municipio = mysqli_real_escape_string($conexion, $_POST['municipio']);

        // Actualizar Alumnos
        $stmtAlumno = $conexion->prepare("UPDATE Alumnos SET Numero_de_Control=?, Nombre_Completo=?, Fecha_de_Nacimiento=?, Sexo=?, Correo_Institucional=?, Correo_Personal=?, Telefono_Celular=?, Ingenieria=?, Estado=?, Municipio=? WHERE id=?");

        if ($stmtAlumno === false) {
            die("Error en la preparación de la consulta para Alumnos: " . $conexion->error);
        }

        $stmtAlumno->bind_param("ssssssisssi", $numControl, $nombre, $fechaNacimiento, $sexo, $correo, $correoP, $telefono, $ingenieria, $estado, $municipio, $idAlumno);
        $stmtAlumno->execute();

        // Actualizar UniversidadEstancia
        $nombreUniversidad = mysqli_real_escape_string($conexion, $_POST['nombre_universidad']);
        $paisUniversidad = mysqli_real_escape_string($conexion, $_POST['pais']);
        $coordinadorUniversidad = mysqli_real_escape_string($conexion, $_POST['coordinador']);
        $telefonoCoordinadorUniversidad = mysqli_real_escape_string($conexion, $_POST['telefono_coordinador']);
        $correoCoordinadorUniversidad = mysqli_real_escape_string($conexion, $_POST['correo_coordinador']);
        $nombreProyectoInvestigacion = mysqli_real_escape_string($conexion, $_POST['nombre_proyecto']); // opcional
        $observacionesUniversidad = mysqli_real_escape_string($conexion, $_POST['observaciones']); // opcional

        $stmtUniversidad = $conexion->prepare("UPDATE UniversidadEstancia SET Nombre_Universidad=?, Pais=?, Coordinador=?, Telefono_Coordinador=?, Correo_Coordinador=?, Nombre_Proyecto_Investigacion=?, Observaciones=? WHERE Alumno_id=?");

        if ($stmtUniversidad === false) {
            die("Error en la preparación de la consulta para UniversidadEstancia: " . $conexion->error);
        }

        $stmtUniversidad->bind_param("sssssssi", $nombreUniversidad, $paisUniversidad, $coordinadorUniversidad, $telefonoCoordinadorUniversidad, $correoCoordinadorUniversidad, $nombreProyectoInvestigacion, $observacionesUniversidad, $idAlumno);
        $stmtUniversidad->execute();

        // Actualizar Estancia
        $tipoPrograma = mysqli_real_escape_string($conexion, $_POST['tipo_programa']);
        $fechaRegistro = mysqli_real_escape_string($conexion, $_POST['fecha_registro']);
        $periodo = mysqli_real_escape_string($conexion, $_POST['Periodo']);
        $promedio = mysqli_real_escape_string($conexion, $_POST['promedio']);
        $semestre = mysqli_real_escape_string($conexion, $_POST['semestre']);
        $idioma = mysqli_real_escape_string($conexion, $_POST['idioma']);
        $nivelIdioma = mysqli_real_escape_string($conexion, $_POST['nivel_idioma']);
        $documentoAval = mysqli_real_escape_string($conexion, $_POST['documento_aval']);
        $fechaExpiracion = mysqli_real_escape_string($conexion, $_POST['fecha_expiracion']);

        // Actualizar Estancia
        $stmtEstancia = $conexion->prepare("UPDATE Estancia SET Tipo_Programa=?, Fecha_Registro=?, Periodo=?, Promedio=?, Semestre=?, Idioma=?, Nivel_Idioma=?, Documento_Aval=?, Fecha_Expiracion=? WHERE Alumno_id=?");

        if ($stmtEstancia === false) {
            die("Error en la preparación de la consulta para Estancia: " . $conexion->error);
        }

        $stmtEstancia->bind_param("ssiiissssi", $tipoPrograma, $fechaRegistro, $periodo, $promedio, $semestre, $idioma, $nivelIdioma, $documentoAval, $fechaExpiracion, $idAlumno);
        $stmtEstancia->execute();

        // Verificar el éxito de las operaciones de actualización
        if ($stmtAlumno->affected_rows > 0 && $stmtUniversidad->affected_rows > 0 && $stmtEstancia->affected_rows > 0) {
            $mensaje = "Información actualizada exitosamente.";
        } else {
            $mensaje = "Error al actualizar la información.";
        }

        // Cerrar los statements
        $stmtAlumno->close();
        $stmtUniversidad->close();
        $stmtEstancia->close();
    }
}
}

// Cierra la conexión después de realizar todas las operaciones
$stmtEditar->close();
$conexion->close();
?>




<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/styles.css">
    <link rel="icon" href="../Assets/alumno.png" type="image/png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto&display=swap">
    <title>Agrgar alumno</title>

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
    <br>
    <br>
    <div class="formulario">
        <form method="POST" action="editar2.php">
            <input type="hidden" name="id" value="<?php echo isset($idEditar) ? $idEditar : ''; ?>">

            <div class="form-group">
                <label for="nombre">Nombre completo:</label>
                <input type="text" name="nombre" value="<?php echo $datosEditar['Nombre_Completo']; ?>">
            </div>

            <div class="form-group">
                <label for="sexo">Sexo:</label>
                <input type="text" name="sexo" required value="<?php echo $datosEditar['Sexo']; ?>"
                    placeholder="Ingrese Masculino o Femenino">
            </div>


            <div class="form-group">
                <label for="num_control">Número de control:</label>
                <input type="text" name="num_control" pattern="[A-Z0-9]{8,9}" required
                    value="<?php echo $datosEditar['Numero_de_Control']; ?>">
            </div>

            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de nacimiento:</label>
                <input type="text" name="fecha_nacimiento" required
                    value="<?php echo $datosEditar['Fecha_de_Nacimiento']; ?>" onblur="validarFormatoFecha(this)">
            </div>

            <div class="form-group">
                <label for="correo">Correo institucional:</label>
                <input type="email" name="correo" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" required
                    value="<?php echo $datosEditar['Correo_Institucional']; ?>">
            </div>

            <div class="form-group">
                <label for="correo">Correo Personal:</label>
                <input type="email" name="Correo_Personal" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                    required value="<?php echo $datosEditar['Correo_Personal']; ?>">
            </div>

            <div class="form-group">
                <label for="telefono">Teléfono celular:</label>
                <input type="tel" name="telefono" pattern="[0-9]{10,12}" required
                    value="<?php echo $datosEditar['Telefono_Celular']; ?>">
            </div>

            <div class="form-group">
                <label for="ingenieria">Ingeniería:</label>
                <input type="text" name="ingenieria" required value="<?php echo $datosEditar['Ingenieria']; ?>">
            </div>

            <!-- Campo Estado -->
            <div class="form-group">
                <label for="estado">Estado:</label>
                <input type="text" name="estado" required value="<?php echo $datosEditar['Estado']; ?>">
            </div>


            <div class="form-group">
                <label for="municipio">Municipio:</label>
                <input type="text" name="municipio" required value="<?php echo $datosEditar['Municipio']; ?>">
            </div>





            <!-- Datos de la universidad de estancia -->

            <div class="form-group">
                <label for="nombre_universidad">Nombre de la universidad:</label>
                <input type="text" name="nombre_universidad" required
                    value="<?php echo $datosEditar['Nombre_Universidad']; ?>">
            </div>

            <div class="form-group">
                <label for="pais">País:</label>
                <input type="text" name="pais" required value="<?php echo $datosEditar['Pais']; ?>">
            </div>

            <div class="form-group">
                <label for="coordinador">Nombre completo del coordinador:</label>
                <input type="text" name="coordinador" required value="<?php echo $datosEditar['Coordinador']; ?>">
            </div>

            <div class="form-group">
                <label for="telefono_coordinador">Teléfono del coordinador:</label>
                <input type="tel" name="telefono_coordinador" pattern="[0-9]{10,12}" required
                    value="<?php echo $datosEditar['Telefono_Coordinador']; ?>">
            </div>

            <div class="form-group">
                <label for="correo_coordinador">Correo del coordinador:</label>
                <input type="email" name="correo_coordinador" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                    required value="<?php echo $datosEditar['Correo_Coordinador']; ?>">
            </div>
            <div class="form-group">
                <label for="nombre_proyecto">Nombre del proyecto de investigación (opcional):</label>
                <input type="text" name="nombre_proyecto" optional
                    value="<?php echo $datosEditar['Nombre_Proyecto_Investigacion']; ?>">
            </div>

            <div class="form-group">
                <label for="observaciones">Observaciones (opcional):</label>
                <textarea name="observaciones" rows="4" cols="50"
                    style="max-width: 100%; max-height: 100px;"><?php echo $datosEditar['Observaciones']; ?></textarea>
            </div>


            <!-- Datos de la estancia -->
            <!-- Campos de la tabla Estancia -->
            <div class="form-group">
                <label for="tipo_programa">Tipo de programa:</label>
                <input type="text" name="tipo_programa" required value="<?php echo $datosEditar['Tipo_Programa']; ?>">
            </div>

            <div class="form-group">
                <label for="fecha_registro">Fecha de registro:</label>
                <input type="text" name="fecha_registro" required value="<?php echo $datosEditar['Fecha_Registro']; ?>"
                    onblur="validarFormatoFecha(this)">
                <!-- Puedes agregar un placeholder que indique el formato de la fecha -->
            </div>

            <div class="form-group">
                <label for="periodo">Periodo:</label>
                <input type="text" name="periodo" required value="<?php echo $datosEditar['Periodo']; ?>">
                <!-- Puedes agregar las opciones de periodo como texto -->
            </div>

            <div class="form-group">
                <label for="promedio">Promedio:</label>
                <input type="number" name="promedio" min="80" max="100" required
                    value="<?php echo $datosEditar['Promedio']; ?>">
            </div>

            <div class="form-group">
                <label for="semestre">Semestre:</label>
                <input type="number" name="semestre" min="4" max="13" required
                    value="<?php echo $datosEditar['Semestre']; ?>">
            </div>

            <div class="form-group">
                <label for="idioma">Idioma:</label>
                <select name="idioma" required>
                    <option value="espanol" <?php echo ($datosEditar['Idioma'] === 'espanol') ? 'selected' : ''; ?>>
                        Español</option>
                    <option value="ingles" <?php echo ($datosEditar['Idioma'] === 'ingles') ? 'selected' : ''; ?>>Inglés
                    </option>
                    <option value="frances" <?php echo ($datosEditar['Idioma'] === 'frances') ? 'selected' : ''; ?>>
                        Francés</option>
                    <option value="aleman" <?php echo ($datosEditar['Idioma'] === 'aleman') ? 'selected' : ''; ?>>Alemán
                    </option>
                    <option value="italiano" <?php echo ($datosEditar['Idioma'] === 'italiano') ? 'selected' : ''; ?>>
                        Italiano</option>
                    <option value="portugues" <?php echo ($datosEditar['Idioma'] === 'portugues') ? 'selected' : ''; ?>>
                        Portugués</option>
                    <option value="chino" <?php echo ($datosEditar['Idioma'] === 'chino') ? 'selected' : ''; ?>>Chino
                    </option>
                    <option value="japones" <?php echo ($datosEditar['Idioma'] === 'japones') ? 'selected' : ''; ?>>
                        Japonés</option>
                    <option value="arabe" <?php echo ($datosEditar['Idioma'] === 'arabe') ? 'selected' : ''; ?>>Árabe
                    </option>
                    <option value="ruso" <?php echo ($datosEditar['Idioma'] === 'ruso') ? 'selected' : ''; ?>>Ruso
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="nivel_idioma">Nivel de idioma:</label>
                <input type="text" name="nivel_idioma" required value="<?php echo $datosEditar['Nivel_Idioma']; ?>">
            </div>

            <div class="form-group">
                <label for="documento_aval">Documento que avala:</label>
                <input type="text" name="documento_aval" required value="<?php echo $datosEditar['Documento_Aval']; ?>">
            </div>

            <div class="form-group">
                <label for="fecha_expiracion">Fecha de expiración:</label>
                <input type="text" name="fecha_expiracion" required
                    value="<?php echo $datosEditar['Fecha_Expiracion']; ?>" onblur="validarFormatoFecha(this)">
                <!-- Puedes agregar un placeholder que indique el formato de la fecha -->
            </div>
            <input type="hidden" name="id" value="<?php echo isset($idEditar) ? $idEditar : ''; ?>">


            <div class="botones">
                <button class="btn-agregar-alumnos" type="submit" name="subir-individual">Actualizar alumno
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