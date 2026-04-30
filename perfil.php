<?php
// --- 1. AUTH WALL (BARREIRA DE SEGURANÇA) ---
// Arranco a sessão logo na linha 1. Se não houver 'user_id', 
// significa que tentaram aceder via URL direto. Chuto-os para a home page.
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <title>StudySpot | O Meu Perfil</title>
    <!-- Framework de CSS (Tailwind) injetada via CDN para manter o projeto sem dependências locais complexas -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-900">

    <!-- ========================================== -->
    <!-- NAVEGAÇÃO SUPERIOR                         -->
    <!-- ========================================== -->
    <nav class="bg-white shadow-sm p-4">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <h1 class="font-bold text-xl text-indigo-600">StudySpot</h1>
            <a href="index.php" class="text-sm font-semibold text-gray-500 hover:text-indigo-600 transition-colors">← Voltar ao Mapa</a>
        </div>
    </nav>

    <!-- ========================================== -->
    <!-- ZONA PRINCIPAL (LISTAGEM DE LOCAIS)        -->
    <!-- ========================================== -->
    <main class="max-w-5xl mx-auto p-6">
        <header class="mb-8">
            <h2 class="text-3xl font-bold">Olá, <?php echo $_SESSION['user_nome']; ?>!</h2>
            <p class="text-gray-500 text-sm">Aqui podes gerir os locais que adicionaste à comunidade.</p>
        </header>

        <section>
            <h3 class="text-lg font-bold mb-4 uppercase text-gray-400 tracking-wider">Os Meus Spots</h3>
            
            <!-- Contentor dinâmico: O JavaScript vai injetar os 'cards' dos locais aqui dentro -->
            <div id="lista-meus-spots" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <p class="text-gray-400 italic">A carregar os teus locais...</p>
            </div>
        </section>
    </main>

    <!-- ========================================== -->
    <!-- MODAL DE EDIÇÃO (UPDATE)                   -->
    <!-- ========================================== -->
    <!-- Escondido por defeito ('hidden'). Só abre quando a função abrirEdicao() é chamada -->
    <div id="modal-editar" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden relative">
            
            <div class="bg-indigo-600 p-4 text-white flex justify-between items-center">
                <h3 class="font-bold text-lg">Editar Local</h3>
                <button onclick="fecharEdicao()" class="text-white hover:text-indigo-200 font-bold text-2xl leading-none">&times;</button>
            </div>
            
            <form id="form-edit-spot" class="p-6 space-y-4">
                <!-- ID escondido: crucial para o backend saber que linha do MySQL vai atualizar -->
                <input type="hidden" id="edit-id" name="id">
                
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Nome do Espaço</label>
                    <input type="text" id="edit-nome" name="nome" required class="w-full border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 p-2 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Tipo</label>
                    <select id="edit-tipo" name="tipo" required class="w-full border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 p-2 text-sm">
                        <option value="Café">Café</option>
                        <option value="Biblioteca">Biblioteca</option>
                        <option value="Cowork">Cowork</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Descrição Breve</label>
                    <textarea id="edit-descricao" name="descricao" required rows="3" class="w-full border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 p-2 text-sm"></textarea>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition mt-2 shadow-md">
                    Guardar Alterações
                </button>
            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MOTOR JAVASCRIPT (LÓGICA CLIENT-SIDE)      -->
    <!-- ========================================== -->
    <script>
        // CACHE DE ESTADO: Guardo aqui os dados que vêm da base de dados.
        // Assim, quando o user clica em 'Editar', não preciso de fazer outro pedido ao servidor 
        // só para preencher o formulário. Leio diretamente desta memória.
        let meusSpotsCache = []; 

        // --- 1. LER DADOS (Read) ---
        async function carregarMeusSpots() {
            const container = document.getElementById('lista-meus-spots');
            
            try {
                // Fetch assíncrono para a API que só me devolve os MEUS spots (vê api/get_my_spots.php)
                const res = await fetch('api/get_my_spots.php');
                meusSpotsCache = await res.json(); 

                // Estado vazio
                if (meusSpotsCache.length === 0) {
                    container.innerHTML = '<div class="bg-white p-6 rounded-xl border border-dashed border-gray-300 text-center col-span-full"><p class="text-gray-400">Ainda não adicionaste nenhum local.</p></div>';
                    return;
                }

                container.innerHTML = ''; // Limpa o estado de "A carregar..."
                
                // Injeção de HTML no DOM para cada local
                meusSpotsCache.forEach(spot => {
                    container.innerHTML += `
                        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition-shadow">
                            <div>
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-lg text-gray-800">${spot.nome}</h4>
                                    <span class="text-[10px] bg-indigo-50 text-indigo-600 border border-indigo-100 px-2 py-1 rounded-full uppercase font-bold">${spot.tipo}</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-4 line-clamp-2">${spot.descricao}</p>
                            </div>
                            
                            <div class="flex gap-2 pt-4 border-t border-gray-50">
                                <!-- Botões com injeção direta do ID do spot nas funções -->
                                <button onclick="abrirEdicao(${spot.id})" class="flex-1 bg-gray-50 hover:bg-indigo-50 hover:text-indigo-700 text-gray-600 border border-gray-200 text-xs font-bold py-2 rounded-lg transition-colors">
                                    Editar
                                </button>
                                <button onclick="confirmarEliminar(${spot.id})" class="flex-1 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold py-2 rounded-lg transition-colors">
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    `;
                });
            } catch (e) {
                container.innerHTML = '<p class="text-red-500">Erro ao comunicar com a base de dados.</p>';
            }
        }

        // --- 2. ATUALIZAR DADOS (Update) ---
        function abrirEdicao(id) {
            // Vai à cache procurar o objeto completo do local clicado
            const spot = meusSpotsCache.find(s => s.id == id);
            if (!spot) return;

            // Preenche o modal
            document.getElementById('edit-id').value = spot.id;
            document.getElementById('edit-nome').value = spot.nome;
            document.getElementById('edit-tipo').value = spot.tipo;
            document.getElementById('edit-descricao').value = spot.descricao;

            // Exibe o modal
            document.getElementById('modal-editar').classList.remove('hidden');
        }

        function fecharEdicao() {
            document.getElementById('modal-editar').classList.add('hidden');
        }

        // Intercetar a submissão do formulário para evitar o refresh da página (Single Page Application UX)
        document.getElementById('form-edit-spot').addEventListener('submit', async function(e) {
            e.preventDefault(); 
            
            // Empacota os dados todos (inputs) num objeto nativo
            const dados = new FormData(this);

            try {
                const res = await fetch('api/update_spot.php', { method: 'POST', body: dados });
                const result = await res.json();

                if (result.sucesso) {
                    fecharEdicao();
                    carregarMeusSpots(); // Recarrega os dados fresquinhos na interface
                } else {
                    alert("Erro do Servidor: " + result.erro);
                }
            } catch (e) {
                alert("Erro de comunicação ao tentar atualizar.");
            }
        });

        // --- 3. APAGAR DADOS (Delete) ---
        function confirmarEliminar(id) {
            // Dupla confirmação para evitar que o utilizador apague dados por engano
            if (confirm("Tens a certeza? Esta ação vai apagar o local e todas as avaliações associadas.")) {
                eliminarSpot(id);
            }
        }

        async function eliminarSpot(id) {
            try {
                const dados = new FormData();
                dados.append('id', id);
                
                const res = await fetch('api/delete_spot.php', { method: 'POST', body: dados });
                const result = await res.json();
                
                if (result.sucesso) { 
                    carregarMeusSpots(); // Remove o card da interface logo a seguir
                } else { 
                    alert("Erro do Servidor: " + result.erro); 
                }
            } catch (e) { 
                alert("Erro grave ao comunicar com a API de eliminação."); 
            }
        }

        // ==========================================
        // ARRANQUE DA APLICAÇÃO
        // ==========================================
        // Assim que o HTML é lido pelo navegador, dispara a função para ir buscar os dados.
        carregarMeusSpots();
    </script>
</body>
</html>