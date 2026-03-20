// Mantiene la referencia de la gráfica actual para poder recrearla sin duplicados.
let chart = null;

// Consulta estadísticas al backend y actualiza la vista con los datos recibidos.
function buscarEstadisticas() {
    const codigo = document.getElementById('codigoInput').value.trim();
    const errorDiv = document.getElementById('error');
    const resultadoDiv = document.getElementById('resultado');

    errorDiv.innerText = '';
    resultadoDiv.style.display = 'none';

    if (!codigo) {
        errorDiv.innerText = 'Ingrese un codigo';
        return;
    }

    // Solicita al backend las estadísticas del código ingresado y espera un JSON.
    fetch(`/api.php?action=estadisticas&codigo=${codigo}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                errorDiv.innerText = data.error;
                return;
            }

            document.getElementById('urlOriginal').innerText = data.urlOriginal;
            document.getElementById('fecha').innerText = data.fecha;
            document.getElementById('totalAccesos').innerText = data.totalAccesos;

            const listaPaises = document.getElementById('listaPaises');
            listaPaises.innerHTML = '';
            if (data.paises.length === 0) {
                listaPaises.innerHTML = '<li>Sin accesos aun</li>';
            } else {
                data.paises.forEach(p => {
                    listaPaises.innerHTML += `<li>${p}</li>`;
                });
            }

            const fechas = Object.keys(data.accesosPorDia);
            const totales = Object.values(data.accesosPorDia);

            // Destruye la gráfica anterior antes de dibujar una nueva.
            if (chart) chart.destroy();

            if (fechas.length > 0) {
                const ctx = document.getElementById('grafica').getContext('2d');
                // Renderiza la gráfica de accesos por día usando Chart.js.
                chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: fechas,
                        datasets: [{
                            label: 'Accesos por dia',
                            data: totales,
                            backgroundColor: 'rgba(102, 126, 234, 0.6)',
                            borderColor: 'rgba(102, 126, 234, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }

            resultadoDiv.style.display = 'block';
        })
        .catch(err => {
            console.error('buscarEstadisticas error:', err);
            errorDiv.innerText = 'Error al conectar con el servidor';
        });
}
