document.addEventListener("DOMContentLoaded", function () {
    // Obtener los datos desde PHP y convertirlos a formato JavaScript
    var sexoLabels = <?php echo json_encode($sexoLabels); ?>;
    var cantidadDatos = <?php echo json_encode($cantidadDatos); ?>;

    // Crear la gráfica de pastel
    var ctx = document.getElementById('graficaPastel').getContext('2d');
    var graficaPastel = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: sexoLabels,
            datasets: [{
                data: cantidadDatos,
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
});
