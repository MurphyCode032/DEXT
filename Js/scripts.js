// scripts.js

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

// scripts.js
google.charts.load('current', { 'packages': ['corechart'] });

function mostrarGraficaCirculo(data) {
    const dataArray = [['Sexo', 'Cantidad']];
    dataArray.push(['Hombres', parseInt(data.cantidad)]);
    dataArray.push(['Mujeres', <?php echo $totalResultados - $data['cantidad']; ?>]); // Calcula la cantidad de mujeres

    const data = google.visualization.arrayToDataTable(dataArray);
    const options = {
        title: 'Porcentaje de Hombres y Mujeres',
    };

    const chart = new google.visualization.PieChart(document.getElementById('grafica'));
    chart.draw(data, options);
}

function mostrarMensaje(mensaje, esError) {
    const mensajeElemento = document.getElementById('mensaje');
    mensajeElemento.style.color = esError ? 'red' : 'green';
    mensajeElemento.textContent = mensaje;
}

// Resto del código de scripts.js (si es necesario)
