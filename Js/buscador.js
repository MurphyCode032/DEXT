// buscador.js
function buscarAlumnos() {
    var input = document.getElementById('campoBusqueda').value;

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById('tablaAlumnos').innerHTML = this.responseText;
        }
    };

    xhr.open('GET', 'buscar_alumnos.php?query=' + input, true);
    xhr.send();
}
