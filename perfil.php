<?php
// --- 1. SEGURANÇA DA SESSÃO ---
session_start();

// Se o utilizador não estiver logado, é redirecionado para a página de login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudySpot | O Meu Perfil</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">

    <nav class="bg-white shadow-sm p-4">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <h1 class="font-bold text-xl text-indigo-600 tracking-tight">StudySpot</h1>
            <a href="index.php" class="text-sm font-semibold text-gray-500 hover:text-indigo-600 transition-colors flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Voltar ao Mapa
            </a>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto p-6">
        
        <header class="mb-10">
            <h2 class="text-3xl font-bold text-gray-800">Olá, <?php echo $_SESSION['user_nome']; ?>!</h2>
            <p class="text-gray-500 text-sm mt-1">Gere os teus locais e as tuas contribuições para a rede StudySpot.</p>
        </header>

        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
            <div class="bg-indigo-600 rounded-2xl p-6 mb-12 text-white flex flex-col md:flex-row justify-between items-center shadow-xl border-b-4 border-indigo-800 transition-all hover:shadow-indigo-200">
                <div class="text-center md:text-left mb-4 md:mb-0">
                    <div class="flex items-center justify-center md:justify-start gap-2 mb-1">
                        <span class="bg-indigo-400 text-white text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-widest">Admin Mode</span>
                        <h4 class="font-bold text-lg">Painel de Controlo</h4>
                    </div>
                    <p class="text-indigo-100 text-xs">Tens acesso a estatísticas e aprovação de novos locais pendentes.</p>
                </div>
                <a href="admin.php" class="bg-white text-indigo-600 px-8 py-3 rounded-xl font-bold text-sm hover:bg-indigo-50 transition-all shadow-md active:scale-95">
                    Gerir Plataforma
                </a>
            </div>
        <?php endif; ?>

        <section>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold uppercase text-gray-400 tracking-widest">Os Meus Spots</h3>
                <span class="h-px bg-gray-200 flex-1 ml-4"></span>
            </div>
            
            <div id="lista-meus-spots" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-full py-12 text-center">
                    <p class="text-gray-400 animate-pulse italic">A carregar os teus locais...</p>
                </div>
            </div>
        </section>
    </main>

    <div id="modal-editar" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4 backdrop-blur-sm transition-all">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden relative border border-gray-100">
            
            <div class="bg-indigo-600 p-5 text-white flex justify-between items-center">
                <h3 class="font-bold text-lg flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Editar Informação
                </h3>
                <button onclick="fecharEdicao()" class="text-white hover:text-indigo-200 font-bold text-2xl leading-none transition-transform hover:rotate-90">&times;</button>
            </div>
            
            <form id="form-edit-spot" class="p-6 space-y-5">
                <input type="hidden" id="edit-id" name="id">
                
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">Nome do Espaço</label>
                    <input type="text" id="edit-nome" name="nome" required class="w-full border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 p-3 text-sm transition-all outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">Tipo de Estabelecimento</label>
                    <select id="edit-tipo" name="tipo" required class="w-full border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 p-3 text-sm outline-none">
                        <option value="Café">Café</option>
                        <option value="Biblioteca">Biblioteca</option>
                        <option value="Cowork">Cowork</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">Descrição do Local</label>
                    <textarea id="edit-descricao" name="descricao" required rows="4" class="w-full border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 p-3 text-sm outline-none" placeholder="O que torna este sítio especial para estudar?"></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-xl transition-all shadow-lg shadow-indigo-100 active:scale-95">
                        Guardar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Cache local para os spots do utilizador (evita chamadas redundantes)
        let meusSpotsCache = []; 

        /**
         * Carrega os spots criados pelo utilizador logado
         */
        async function carregarMeusSpots() {
            const container = document.getElementById('lista-meus-spots');
            
            try {
                const res = await fetch('api/get_my_spots.php');
                meusSpotsCache = await res.json(); 

                if (meusSpotsCache.length === 0) {
                    container.innerHTML = `
                        <div class="bg-white p-10 rounded-2xl border-2 border-dashed border-gray-200 text-center col-span-full">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-gray-500 font-medium">Ainda não adicionaste nenhum local ao mapa.</p>
                            <p class="text-gray-400 text-xs mt-1">Partilha os teus spots favoritos com a comunidade!</p>
                        </div>`;
                    return;
                }

                container.innerHTML = '';
                
                meusSpotsCache.forEach(spot => {
                    // Badge visual baseada no estado de aprovação (MySQL 'status' column)
                    let statusBadge = '';
                    if (spot.status == 1) {
                        statusBadge = `<span class="text-[9px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-bold uppercase tracking-tighter">Aprovado</span>`;
                    } else if (spot.status == 2) {
                        statusBadge = `<span class="text-[9px] bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-bold uppercase tracking-tighter">Rejeitado</span>`;
                    } else {
                        statusBadge = `<span class="text-[9px] bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full font-bold uppercase tracking-tighter">Em Revisão</span>`;
                    }

                    container.innerHTML += `
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-xl hover:border-indigo-100 transition-all group">
                            <div>
                                <div class="flex justify-between items-start mb-3">
                                    <div class="overflow-hidden">
                                        <h4 class="font-bold text-lg text-gray-800 truncate group-hover:text-indigo-600 transition-colors">${spot.nome}</h4>
                                        <p class="text-[10px] text-indigo-400 font-bold uppercase tracking-widest">${spot.tipo}</p>
                                    </div>
                                    <div class="flex flex-col items-end gap-1 shrink-0">
                                        ${statusBadge}
                                    </div>
                                </div>
                                <p class="text-sm text-gray-500 mb-6 line-clamp-2 leading-relaxed italic border-l-2 border-gray-100 pl-3">
                                    "${spot.descricao}"
                                </p>
                            </div>
                            
                            <div class="flex gap-3 pt-4 border-t border-gray-50">
                                <button onclick="abrirEdicao(${spot.id})" class="flex-1 bg-gray-50 hover:bg-indigo-50 hover:text-indigo-700 text-gray-600 text-xs font-bold py-3 rounded-xl transition-all border border-gray-100 flex items-center justify-center gap-2">
                                    Editar
                                </button>
                                <button onclick="confirmarEliminar(${spot.id})" class="flex-1 bg-red-50 hover:bg-red-500 hover:text-white text-red-600 text-xs font-bold py-3 rounded-xl transition-all flex items-center justify-center gap-2">
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    `;
                });
            } catch (e) {
                container.innerHTML = '<p class="text-red-500 text-center col-span-full">Erro ao sincronizar dados com o servidor.</p>';
            }
        }

        // --- LÓGICA DE EDIÇÃO ---
        function abrirEdicao(id) {
            const spot = meusSpotsCache.find(s => s.id == id);
            if (!spot) return;

            document.getElementById('edit-id').value = spot.id;
            document.getElementById('edit-nome').value = spot.nome;
            document.getElementById('edit-tipo').value = spot.tipo;
            document.getElementById('edit-descricao').value = spot.descricao;

            document.getElementById('modal-editar').classList.remove('hidden');
        }

        function fecharEdicao() {
            document.getElementById('modal-editar').classList.add('hidden');
        }

        // Submissão do formulário via AJAX (sem recarregar a página)
        document.getElementById('form-edit-spot').addEventListener('submit', async function(e) {
            e.preventDefault();
            const dados = new FormData(this);

            try {
                const res = await fetch('api/update_spot.php', { method: 'POST', body: dados });
                const result = await res.json();

                if (result.sucesso) {
                    fecharEdicao();
                    carregarMeusSpots(); // Refresh da lista
                } else {
                    alert("Erro ao atualizar: " + result.erro);
                }
            } catch (e) {
                alert("Erro de comunicação com o servidor.");
            }
        });

        // --- LÓGICA DE ELIMINAÇÃO ---
        function confirmarEliminar(id) {
            if (confirm("Tens a certeza que pretendes eliminar este local permanentemente?")) {
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
                    carregarMeusSpots();
                } else {
                    alert("Erro ao eliminar: " + result.erro);
                }
            } catch (e) {
                alert("Ocorreu um problema ao processar o pedido.");
            }
        }

        // Inicialização automática
        carregarMeusSpots();
    </script>
</body>
</html> 