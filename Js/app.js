// js/app.js

// 1. Iniciar o Mapa centrado em Lisboa
const mapa = L.map('meuMapa').setView([38.7369, -9.1388], 13);

// 2. Carregar as imagens do mapa (Roteamento open-source)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap'
}).addTo(mapa);

const divLista = document.getElementById('lista-spots');

// 3. Função assíncrona para ir buscar os dados REAIS ao MySQL via PHP
async function carregarSpots() {
    try {
        const resposta = await fetch('api/get_spots.php');
        const spots = await resposta.json();

        // Limpa a lista primeiro para não acumular lixo
        divLista.innerHTML = '';

        if(spots.erro) {
            divLista.innerHTML = `<p class="text-red-500 text-sm font-bold">Erro do Servidor: ${spots.erro}</p>`;
            return;
        }

        if(spots.length === 0) {
            divLista.innerHTML = `<p class="text-gray-500 text-sm">Nenhum local encontrado na base de dados.</p>`;
            return;
        }

        // 4. Desenhar os Locais no Mapa e na Barra Lateral
        spots.forEach(spot => {
            // Colocar o Ponto Azul no Mapa
            L.marker([spot.lat, spot.lng]).addTo(mapa)
                .bindPopup(`<b class="text-indigo-600">${spot.nome}</b><br>${spot.tipo}`);

 // Formatar as médias (garante que não dá erro se vierem nulas)
            const ruido = parseFloat(spot.media_ruido || 0).toFixed(1);
            const tomadas = parseFloat(spot.media_tomadas || 0).toFixed(1);
            const wifi = parseFloat(spot.media_wifi || 0).toFixed(1);
            const lotacao = parseFloat(spot.media_lotacao || 0).toFixed(1); // <-- EXTRAÍDO AQUI

            // Injetar o HTML do Cartão
            const cardHTML = `
                <div class="border border-gray-200 p-4 rounded-lg hover:shadow-md cursor-pointer transition bg-white">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg">${spot.nome}</h4>
                            <span class="text-xs text-indigo-600 bg-indigo-50 px-2 py-1 rounded font-semibold uppercase">${spot.tipo}</span>
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2 text-sm text-gray-600 bg-gray-50 p-2 rounded">
                        <span>🤫 Ruído: <strong>${ruido}</strong>/5</span>
                        <span>🔋 Tomadas: <strong>${tomadas}</strong>/5</span>
                        <span>📶 Wi-Fi: <strong>${wifi}</strong>/5</span>
                        <span>👥 Lotação: <strong>${lotacao}</strong>/5</span> </div>
                    <button onclick="abrirAvaliacao(${spot.id}, '${spot.nome}')" class="mt-3 w-full bg-indigo-100 hover:bg-indigo-600 hover:text-white text-indigo-800 text-xs font-bold py-2 rounded transition">
                        Ver e Avaliar
                    </button>
                </div>
            `;
            divLista.innerHTML += cardHTML;
        });

    } catch (erro) {
        console.error("Falha na comunicação com a API:", erro);
        divLista.innerHTML = `<p class="text-red-500 text-sm">Falha de ligação ao servidor. Verifica a consola.</p>`;
    }
}

// Executar a função imediatamente ao abrir o site
carregarSpots();

// 5. Função temporária para testar a inserção de Reviews
function abrirAvaliacao(spotId, nome) {
    const nota = prompt(`Avalia o Ruído em ${nome} (1 a 5):`);
    
    // Cancela se o utilizador não escrever nada
    if (!nota) return; 

    const dados = new FormData();
    dados.append('spot_id', spotId);
    dados.append('ruido', nota);
    dados.append('wifi', 5); // Fixo por agora para testar o backend
    dados.append('tomadas', 5);
    dados.append('comentario', 'Avaliação rápida de teste');
    dados.append('lotacao', 3); // Fixo por agora para testar

    fetch('api/submit_review.php', {
        method: 'POST',
        body: dados
    })
    .then(r => r.json())
    .then(res => {
        if(res.sucesso) {
            alert("Avaliação guardada com sucesso!");
            location.reload(); // Recarrega a página para atualizar as médias
        } else {
            alert("Erro ao avaliar: " + res.erro);
        }
    });
}