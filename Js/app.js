// js/app.js

// ==========================================
// 1. MEMÓRIA GLOBAL E MAPA
// ==========================================

// Memória global para não termos de pedir ao servidor sempre que clicamos num local
window.spotsAtuais = []; 

// Iniciar o Mapa centrado em Lisboa
const mapa = L.map('meuMapa').setView([38.7369, -9.1388], 13);

// Carregar as imagens do mapa (Roteamento open-source)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap'
}).addTo(mapa);

const divLista = document.getElementById('lista-spots');

// ==========================================
// 2. MOTOR DE DADOS (LER DA API)
// ==========================================

async function carregarSpots() {
    try {
        const resposta = await fetch('api/get_spots.php');
        const spots = await resposta.json();
        
        // Guardar os dados na nossa memória global
        window.spotsAtuais = spots; 

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

        // Desenhar os Locais no Mapa e na Barra Lateral
        spots.forEach(spot => {
            // Colocar o Ponto Azul no Mapa
            L.marker([spot.lat, spot.lng]).addTo(mapa)
                .bindPopup(`<b class="text-indigo-600">${spot.nome}</b><br>${spot.tipo}`);

            // Formatar as médias (garante que não dá erro se vierem nulas)
            const ruido = parseFloat(spot.media_ruido || 0).toFixed(1);
            const tomadas = parseFloat(spot.media_tomadas || 0).toFixed(1);
            const wifi = parseFloat(spot.media_wifi || 0).toFixed(1);
            const lotacao = parseFloat(spot.media_lotacao || 0).toFixed(1);

            // Injetar o HTML do Cartão (passamos APENAS o ID para o botão)
            const cardHTML = `
                <div class="border border-gray-200 p-4 rounded-lg hover:shadow-md transition bg-white mb-3">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="font-bold text-gray-800 text-lg">${spot.nome}</h4>
                        <span class="text-[10px] text-indigo-600 bg-indigo-50 px-2 py-1 rounded font-bold uppercase border border-indigo-100">${spot.tipo}</span>
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


// ==========================================
// 3. LÓGICA DO MODAL DE DETALHES (VER O LOCAL)
// ==========================================

const modalDetalhes = document.getElementById('modal-detalhes');

async function abrirDetalhes(idProcurado) {
    // 1. Vai buscar o spot à nossa memória global
    const spot = window.spotsAtuais.find(s => s.id === idProcurado);
    if (!spot) return;

    // 2. Injeta os dados base no HTML do Modal
    document.getElementById('detalhes-nome').innerText = spot.nome;
    document.getElementById('detalhes-tipo').innerText = spot.tipo;
    document.getElementById('detalhes-descricao').innerText = spot.descricao || "Sem descrição disponível para este local.";
    
    document.getElementById('detalhes-ruido').innerText = parseFloat(spot.media_ruido || 0).toFixed(1);
    document.getElementById('detalhes-tomadas').innerText = parseFloat(spot.media_tomadas || 0).toFixed(1);
    document.getElementById('detalhes-wifi').innerText = parseFloat(spot.media_wifi || 0).toFixed(1);
    document.getElementById('detalhes-lotacao').innerText = parseFloat(spot.media_lotacao || 0).toFixed(1);

    // 3. NOVO: Carregar Comentários da Base de Dados
    const divComentarios = document.getElementById('lista-comentarios');
    
    if (divComentarios) {
        // Estado de loading visual para não parecer que a app encravou
        divComentarios.innerHTML = '<p class="text-xs text-gray-400 italic text-center py-4">A carregar opiniões da comunidade...</p>';
        
        try {
            const resposta = await fetch(`api/get_reviews.php?spot_id=${idProcurado}`);
            const reviews = await resposta.json();

            divComentarios.innerHTML = ''; // Limpa o "loading"

            if (reviews.erro) {
                divComentarios.innerHTML = `<p class="text-xs text-red-500">${reviews.erro}</p>`;
            } else if (reviews.length === 0) {
                divComentarios.innerHTML = '<p class="text-xs text-gray-400 italic text-center py-2">Ainda ninguém comentou este espaço. Sê o primeiro!</p>';
            } else {
                // Iterar sobre as reviews e desenhar o HTML de cada uma
                reviews.forEach(rev => {
                    const dataFormatada = new Date(rev.data_review.replace(' ', 'T')).toLocaleDateString('pt-PT');
                    
                    divComentarios.innerHTML += `
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 mb-2">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-[10px] font-bold text-indigo-600 uppercase">${rev.autor}</span>
                                <span class="text-[9px] text-gray-400">${dataFormatada}</span>
                            </div>
                            <p class="text-xs text-gray-700 leading-relaxed">${rev.comentario}</p>
                        </div>
                    `;
                });
            }
        } catch (erro) {
            console.error("Erro ao buscar comentários:", erro);
            divComentarios.innerHTML = '<p class="text-xs text-red-500">Falha ao carregar comentários. Verifica a tua ligação.</p>';
        }
    }

    // 4. Configura o botão de avaliar para fechar este modal e abrir o outro
    const btnAvaliar = document.getElementById('btn-abrir-avaliacao');
    if (btnAvaliar) {
        btnAvaliar.onclick = function() {
            fecharDetalhes(); 
            abrirAvaliacao(spot.id, spot.nome); 
        };
    }

    // 5. Mostra o modal no ecrã
    if (modalDetalhes) modalDetalhes.classList.remove('hidden');
}

function fecharDetalhes() {
    if(modalDetalhes) modalDetalhes.classList.add('hidden');
}


// ==========================================
// 4. LÓGICA DO MODAL DE AVALIAÇÃO (CRIAR REVIEW)
// ==========================================

const modalAvaliacao = document.getElementById('modal-avaliacao');
const formReview = document.getElementById('form-review');

// Abre a janela e injeta o nome e ID do local
function abrirAvaliacao(spotId, nome) {
    // 1. A Auth Wall (Bloqueio de anónimos)
    if (!utilizadorLogado) {
        alert("Precisas de ter conta para avaliar os espaços. Vamos levar-te para o Login!");
        window.location.href = 'login.php'; // Redireciona para a página de login
        return; // Impede que o resto do código corra e abra o modal
    }

    // 2. Se estiver logado, o código continua normalmente
    document.getElementById('modal-spot-id').value = spotId;
    document.getElementById('modal-nome-spot').innerText = nome;
    
    if(modalAvaliacao) modalAvaliacao.classList.remove('hidden');
}

// Fecha a janela e limpa o formulário para a próxima vez
function fecharModal() {
    if(modalAvaliacao) modalAvaliacao.classList.add('hidden');
    if(formReview) formReview.reset(); 
}

// Interceta o clique no botão "Enviar Avaliação" para não recarregar a página
if(formReview) {
    formReview.addEventListener('submit', function(e) {
        e.preventDefault(); 
        
        const dados = new FormData(formReview);

        fetch('api/submit_review.php', {
            method: 'POST',
            body: dados
        })
        .then(r => r.json())
        .then(res => {
            if(res.sucesso) {
                fecharModal();
                // Vai buscar as médias atualizadas ao MySQL e redesenha a barra lateral
                carregarSpots(); 
                alert("Avaliação guardada com sucesso! Obrigado.");
            } else {
                alert("Erro: " + (res.erro || "Falha ao submeter."));
            }
        })
        .catch(erro => {
            console.error("Erro na rede:", erro);
            alert("Erro ao comunicar com o servidor.");
        });
    });
}
