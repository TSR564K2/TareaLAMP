// Envía la URL al backend y muestra el resultado devuelto por la API.
function shortenUrl() {
    const input = document.getElementById('urlInput').value;

    if (!input) {
        alert('Ingrese una URL');
        return;
    }

    // Envía la URL original al endpoint de acortado y espera una respuesta JSON.
    fetch('../api.php?action=acortar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ urlOriginal: input })
    })
    .then(response => response.json())
    .then(data => {
        const resultDiv = document.getElementById('result');
        const resultText = document.getElementById('resultText');

        if (data.shortUrl) {
            resultText.innerHTML = `
                URL corta: 
                <a href="${data.shortUrl}" target="_blank">
                    ${data.shortUrl}
                </a>
            `;
        } else {
            resultText.innerText = data.error || 'Error';
        }
        resultDiv.style.display = 'block';
    })
    .catch(error => {
        console.error(error);
    });
}

// Navega hacia la vista de estadísticas.
function verEstadisticas() {
    window.location.href = '/vista/estadisticas.html';
}

// Inicializa eventos y scripts según la vista parcial cargada.

function initView(url) {
  // cargar script de estadísticas si no está cargado
    if (!window.estadisticasScriptCargado) {
      window.estadisticasScriptCargado = true;
      var s = document.createElement('script');
      s.src = 'estadisticas.js';
      s.onload = function () {
        const botonBuscar = document.getElementById('botonBuscar');
        if (botonBuscar) botonBuscar.onclick = buscarEstadisticas;
      };
      document.body.appendChild(s);
    }
    
    if (url.includes('estadisticas.html')) {
    const btnBack = document.getElementById('btnBack');
    if (btnBack) btnBack.onclick = () => cargarVistaParcial('index.html');

    const botonBuscar = document.getElementById('botonBuscar');
    if (botonBuscar && typeof buscarEstadisticas === 'function') botonBuscar.onclick = buscarEstadisticas;

    
  } else {
    const btnStats = document.getElementById('btnStats');
    if (btnStats) btnStats.onclick = () => cargarVistaParcial('estadisticas.html');

    const btnShorten = document.getElementById('btnShorten');
    if (btnShorten) btnShorten.onclick = shortenUrl;
  }
}

// Carga una vista HTML parcial y reemplaza el contenido del contenedor principal.
function cargarVistaParcial(url) {
  // Solicita el HTML de la vista parcial y lo inserta dentro de #container.
  fetch(url)
    .then(res => res.text())
    .then(html => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      const cont = doc.querySelector('#container');
      if (!cont) {
        throw new Error('No se encontró #container en ' + url);
      }
      document.querySelector('#container').innerHTML = cont.innerHTML;
      document.title = doc.title || document.title;
      initView(url);
    })
    .catch(err => console.error('cargarVistaParcial', err));

}
