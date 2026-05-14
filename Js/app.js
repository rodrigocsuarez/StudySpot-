
// 1. INICIALIZAÇÃO DO MAPA E ESTADO GLOBAL


// Memória local (Cache). Guarda os spots para não termos de ir à base de dados 
// de cada vez que o utilizador clica em "Ver Detalhes".
window.spotsAtuais = []; 

// Iniciar o mapa (Coordenadas padrão: Lisboa)
const mapa = L.map('meuMapa').setView([38.7223, -9.1393], 13);

// Camada visual do mapa (OpenStreetMap)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(mapa);

// Tentar localizar o utilizador uma única vez no arranque (Melhora a UX)
if ("geolocation" in navigator) {
    navigator.geolocation.getCurrentPosition(
        posicao => {
            const lat = posicao.coords.latitude;
            const lng = posicao.coords.longitude;
            mapa.setView([lat, lng], 14); // Centra no utilizador
            
            // Desenha um ponto verde para o user saber onde está
            L.circleMarker([lat, lng], {
                color: '#16a34a', fillColor: '#16a34a', fillOpacity: 0.5, radius: 8
            }).addTo(mapa).bindPopup("Estás aqui!").openPopup();
        }, 
        erro => {
            console.warn("Localização falhou ou foi recusada. Mantém em Lisboa.");
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}


// 2. CARREGAMENTO DOS LOCAIS (READ)


// Grupo de marcadores para podermos limpar o mapa antes de atualizar
let layerMarcadores = L.layerGroup().addTo(mapa);

async function carregarSpots() {
    try {
        const res = await fetch('api/get_spots.php');
        const spots = await res.json();
        
        window.spotsAtuais = spots; // Guarda na cache global
        
        layerMarcadores.clearLayers(); // Limpa os pontos antigos do mapa
        const listaSpots = document.getElementById('lista-spots');
        listaSpots.innerHTML = ''; // Limpa a barra lateral

        if (spots.length === 0) {
            listaSpots.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">Ainda não há locais registados.</p>';
            return;
        }

        spots.forEach(spot => {
            // 1. Desenhar o Ponto no Mapa
            const marcador = L.marker([spot.lat, spot.lng]).bindPopup(`
                <div class="text-center">
                    <strong class="block mb-1">${spot.nome}</strong>
                    <button onclick="abrirDetalhes(${spot.id})" class="text-xs bg-indigo-600 text-white px-2 py-1 rounded">Ver Espaço</button>
                </div>
            `);
            layerMarcadores.addLayer(marcador);

            // Tratamento visual das notas matemáticas (0 vira traço)
            const ruido = spot.media_ruido > 0 ? parseFloat(spot.media_ruido).toFixed(1) : '-';
            const tomadas = spot.media_tomadas > 0 ? parseFloat(spot.media_tomadas).toFixed(1) : '-';
            const wifi = spot.media_wifi > 0 ? parseFloat(spot.media_wifi).toFixed(1) : '-';
            const lotacao = spot.media_lotacao > 0 ? parseFloat(spot.media_lotacao).toFixed(1) : '-';

            // 2. Lógica de Imagem no Cartão (Mostra foto ou placeholder)
            const imagemSpot = spot.imagem_url 
                ? `<img src="${spot.imagem_url}" class="w-full h-32 object-cover rounded-t-lg border-b border-gray-100">`
                : `<div class="w-full h-32 bg-indigo-50 flex items-center justify-center rounded-t-lg border-b border-indigo-100"><span class="text-indigo-300 text-xs font-bold uppercase tracking-widest">Sem Imagem</span></div>`;

            // 3. Injetar o Cartão na Barra Lateral
            listaSpots.innerHTML += `
                <div class="border border-gray-200 rounded-lg hover:shadow-md transition bg-white mb-3 flex flex-col">
                    ${imagemSpot}
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-bold text-gray-800 text-lg line-clamp-1">${spot.nome}</h4>
                            <span class="text-[10px] text-indigo-600 bg-indigo-50 px-2 py-1 rounded font-bold uppercase border border-indigo-100 shrink-0 ml-2">${spot.tipo}</span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs text-gray-600 bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <div class="flex items-center justify-between"><span>🤫 Ruído:</span> <strong>${ruido}</strong></div>
                            <div class="flex items-center justify-between"><span>🔋 Tomadas:</span> <strong>${tomadas}</strong></div>
                            <div class="flex items-center justify-between"><span>📶 Wi-Fi:</span> <strong>${wifi}</strong></div>
                            <div class="flex items-center justify-between"><span>👥 Lotação:</span> <strong>${lotacao}</strong></div>
                        </div>

                        <button onclick="abrirDetalhes(${spot.id})" class="mt-4 w-full bg-white border-2 border-indigo-600 text-indigo-600 hover:bg-indigo-50 font-bold py-2 rounded-lg transition shadow-sm">
                            Ver Detalhes
                        </button>
                    </div>
                </div>
            `;
        });

    } catch (erro) {
        console.error("Erro a carregar spots:", erro);
        document.getElementById('lista-spots').innerHTML = '<p class="text-red-500 text-sm">Erro ao carregar os dados.</p>';
    }
}


// 3. MODAL DE DETALHES E COMENTÁRIOS


async function abrirDetalhes(idProcurado) {
    // 1. Vai buscar os dados à Cache (não sobrecarrega a DB)
    const spot = window.spotsAtuais.find(s => s.id === idProcurado);
    if (!spot) return;

    // 2. Preenche os Textos
    document.getElementById('detalhes-nome').innerText = spot.nome;
    document.getElementById('detalhes-tipo').innerText = spot.tipo;
    document.getElementById('detalhes-descricao').innerText = spot.descricao || "Sem descrição disponível.";
    
    // 3. Gestão Inteligente da Imagem de Capa
    const imgContainer = document.getElementById('detalhes-imagem-container');
    const imgElement = document.getElementById('detalhes-imagem');
    const btnFecharSemImagem = document.getElementById('btn-fechar-sem-imagem');

    if (spot.imagem_url) {
        imgElement.src = spot.imagem_url;
        imgContainer.classList.remove('hidden');
        btnFecharSemImagem.classList.add('hidden'); // Esconde o botão de fechar do cabeçalho
    } else {
        imgContainer.classList.add('hidden');
        btnFecharSemImagem.classList.remove('hidden'); // Mostra o botão de fechar do cabeçalho
        imgElement.src = ''; // Limpa para não dar glitch na próxima abertura
    }

    // 4. Injeta as Médias
    document.getElementById('detalhes-ruido').innerText = parseFloat(spot.media_ruido || 0).toFixed(1);
    document.getElementById('detalhes-tomadas').innerText = parseFloat(spot.media_tomadas || 0).toFixed(1);
    document.getElementById('detalhes-wifi').innerText = parseFloat(spot.media_wifi || 0).toFixed(1);
    document.getElementById('detalhes-lotacao').innerText = parseFloat(spot.media_lotacao || 0).toFixed(1);

    // 5. Mostra o Modal
    document.getElementById('modal-detalhes').classList.remove('hidden');

    // 6. Prepara o botão de Avaliar (Guarda o ID no botão para quando for clicado)
    const btnAvaliar = document.getElementById('btn-abrir-avaliacao');
    btnAvaliar.onclick = function() {
        if (!utilizadorLogado) {
            alert("Precisas de ter sessão iniciada para avaliar.");
            window.location.href = 'login.php';
            return;
        }
        document.getElementById('modal-spot-id').value = spot.id;
        document.getElementById('modal-nome-spot').innerText = spot.nome;
        document.getElementById('modal-detalhes').classList.add('hidden');
        document.getElementById('modal-avaliacao').classList.remove('hidden');
    };

    // 7. Carregar Comentários da Base de Dados (Assíncrono)
    const listaComentarios = document.getElementById('lista-comentarios');
    listaComentarios.innerHTML = '<p class="text-xs text-gray-400 italic">A carregar...</p>';

    try {
        const resReviews = await fetch(`api/get_reviews.php?spot_id=${spot.id}`);
        const reviews = await resReviews.json();

        listaComentarios.innerHTML = '';
        if (reviews.length === 0) {
            listaComentarios.innerHTML = '<p class="text-xs text-gray-400 italic">Ainda não há comentários.</p>';
        } else {
            reviews.forEach(r => {
                // Formata a data (Ex: "2024-05-14 12:30" para algo legível)
                const dataFormatada = new Date(r.data_review).toLocaleDateString('pt-PT', {day:'numeric', month:'short', year:'numeric'});
                
                listaComentarios.innerHTML += `
                    <div class="bg-gray-50 p-3 rounded border border-gray-100">
                        <div class="flex justify-between items-center mb-1">
                            <strong class="text-xs text-gray-800">${r.user_nome}</strong>
                            <span class="text-[10px] text-gray-400">${dataFormatada}</span>
                        </div>
                        ${r.comentario ? `<p class="text-xs text-gray-600 mt-1">"${r.comentario}"</p>` : ''}
                    </div>
                `;
            });
        }
    } catch (e) {
        listaComentarios.innerHTML = '<p class="text-xs text-red-400">Erro ao carregar comentários.</p>';
    }
}

function fecharDetalhes() {
    document.getElementById('modal-detalhes').classList.add('hidden');
}

function fecharModal() {
    document.getElementById('modal-avaliacao').classList.add('hidden');
    document.getElementById('form-review').reset();
}

// Intercetar envio da Avaliação (Para não haver reload da página)
const formReview = document.getElementById('form-review');
if (formReview) {
    formReview.addEventListener('submit', async function(e) {
        e.preventDefault();
        const dados = new FormData(this);

        try {
            const res = await fetch('api/submit_review.php', { method: 'POST', body: dados });
            const result = await res.json();

            if (result.sucesso) {
                fecharModal();
                carregarSpots(); // Recalcula as médias e atualiza a interface
                alert("Avaliação guardada com sucesso!");
            } else {
                alert("Erro: " + result.erro);
            }
        } catch (erro) {
            alert("Erro de ligação ao servidor.");
        }
    });
}


// 4. CRIAÇÃO DE NOVOS LOCAIS (CREATE)


let modoCriacaoAtivo = false;
let marcadorUser = null; 
const modalCriacao = document.getElementById('modal-criacao');
const formCreateSpot = document.getElementById('form-create-spot');

// 1. Ativar modo escuta
document.getElementById('btn-ativar-criacao').addEventListener('click', () => {
    if (!utilizadorLogado) {
        alert("Precisas de ter sessão iniciada para adicionar locais.");
        window.location.href = 'login.php';
        return;
    }

    modoCriacaoAtivo = true;
    document.getElementById('meuMapa').style.cursor = 'crosshair';
    alert("Clica no local exato do mapa para adicionar o espaço.");
});

// 2. Intercetar o clique no mapa
mapa.on('click', function(e) {
    if (!modoCriacaoAtivo) return; 
    
    modoCriacaoAtivo = false;
    document.getElementById('meuMapa').style.cursor = ''; // Restaura o cursor

    // Injeta as coordenadas capturadas nos inputs escondidos do formulário
    document.getElementById('create-lat').value = e.latlng.lat;
    document.getElementById('create-lng').value = e.latlng.lng;
    
    modalCriacao.classList.remove('hidden');
});

function fecharCriacao() {
    modalCriacao.classList.add('hidden');
    formCreateSpot.reset();
}

// 3. Submeter formulário de Criação (Suporta imagens graças ao FormData)
if (formCreateSpot) {
    formCreateSpot.addEventListener('submit', async function(e) {
        e.preventDefault(); 
        
        // O FormData captura todos os inputs, incluindo o ficheiro de imagem
        const dados = new FormData(this);

        try {
            const res = await fetch('api/create_spot.php', { method: 'POST', body: dados });
            const result = await res.json();

            if (result.sucesso) {
                fecharCriacao();
                carregarSpots(); // Redesenha o mapa para mostrar o local novo imediatamente
                alert("Local gravado com sucesso no mapa!");
            } else {
                alert("Erro: " + result.erro);
            }
        } catch (erro) {
            alert("Erro ao comunicar com o servidor.");
        }
    });
}


// ARRANQUE

// Puxa os locais todos da base de dados logo que o JavaScript carrega.
carregarSpots();