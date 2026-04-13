// js/app.js

// 1. Iniciar o Mapa centrado em Lisboa
const mapa = L.map('meuMapa').setView([38.7369, -9.1388], 13);

// 2. Carregar as imagens do mapa (Roteamento open-source)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap'
}).addTo(mapa);

// 3. Os nossos Dados Falsos (Enquanto o PHP não envia os verdadeiros)
const spotsFalsos = [
    {
        nome: "Café Académico",
        tipo: "Café",
        lat: 38.7369,
        lng: -9.1388,
        rating_ruido: 4,
        rating_tomadas: 5
    },
    {
        nome: "Biblioteca Municipal",
        tipo: "Biblioteca",
        lat: 38.7320,
        lng: -9.1420,
        rating_ruido: 5,
        rating_tomadas: 2
    }
];

// 4. Função para desenhar os marcadores e os cards
const divLista = document.getElementById('lista-spots');

spotsFalsos.forEach(spot => {
    // Colocar um Ponto Azul no Mapa
    L.marker([spot.lat, spot.lng]).addTo(mapa)
        .bindPopup(`<b>${spot.nome}</b><br>${spot.tipo}`);

    // Injetar um Card HTML na barra lateral para cada spot
    const cardHTML = `
        <div class="border border-gray-200 p-4 rounded-lg hover:shadow-md cursor-pointer transition bg-white">
            <div class="flex justify-between items-start">
                <div>
                    <h4 class="font-bold text-gray-800 text-lg">${spot.nome}</h4>
                    <span class="text-xs text-indigo-600 bg-indigo-50 px-2 py-1 rounded font-semibold uppercase">${spot.tipo}</span>
                </div>
            </div>
            <div class="mt-3 flex gap-4 text-sm text-gray-600">
                <span>🤫 Ruído: ${spot.rating_ruido}/5</span>
                <span>🔋 Tomadas: ${spot.rating_tomadas}/5</span>
            </div>
        </div>
    `;
    divLista.innerHTML += cardHTML;
});