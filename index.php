<?php
session_start();

// Verificar se existe um utilizador na sessão
$esta_logado = isset($_SESSION['user_nome']);

// Se estiver logado, apanhamos a primeira letra do nome
$inicial = "";
if ($esta_logado) {
    // mb_substr garante que funciona com acentos, 0 é a posição, 1 é a quantidade de letras
    $inicial = mb_strtoupper(mb_substr($_SESSION['user_nome'], 0, 1));
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudySpot - Encontra o teu local</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body class="h-screen flex flex-col bg-gray-50 overflow-hidden">

    <header class="bg-indigo-600 text-white p-4 shadow-md flex justify-between items-center z-20 relative">
        <h1 class="text-2xl font-bold text-white"><a href="index.php">StudySpot</a></h1>
        
        <div class="space-x-2 md:space-x-4 flex items-center">
            <input type="text" placeholder="Procurar zona..." class="hidden md:block px-3 py-1 rounded text-gray-800 text-sm focus:outline-none">
            
            <?php if ($esta_logado): ?>
                <div class="flex items-center gap-3">
                    <span class="hidden md:block text-sm font-medium">Olá, <?php echo $_SESSION['user_nome']; ?></span>
                    <button class="flex items-center justify-center w-9 h-9 rounded-full bg-indigo-800 text-white font-bold border border-indigo-400 shadow-sm">
                        <?php echo $inicial; ?>
                    </button>
                    <a href="api/logout.php" class="text-xs text-indigo-200 hover:text-white underline">Sair</a>
                </div>
            <?php else: ?>
                <a href="login.php" class="bg-indigo-800 hover:bg-indigo-700 px-4 py-1 rounded font-semibold text-sm transition">
                    Login
                </a>
            <?php endif; ?>
        </div>
    </header>

    <div class="flex flex-col md:flex-row flex-1 h-full relative">
        
        <main class="w-full md:w-2/3 min-h-[50vh] md:h-full relative z-0 order-1 md:order-2">
            <div id="meuMapa" class="absolute inset-0 w-full h-full"></div>
        </main>

        <aside class="w-full md:w-1/3 h-1/2 md:h-full bg-white shadow-lg z-10 p-4 md:p-6 overflow-y-auto flex flex-col order-2 md:order-1">
            <h2 class="text-lg font-bold mb-4 text-gray-800">O que precisas hoje?</h2>
            
            <div class="flex flex-wrap items-center gap-2 mb-6 py-2">
                <button class="bg-gray-200 hover:bg-indigo-100 text-gray-700 px-4 py-2 rounded-full text-sm font-medium transition shadow-sm">🔋 Só Tomadas</button>
                <button class="bg-gray-200 hover:bg-indigo-100 text-gray-700 px-4 py-2 rounded-full text-sm font-medium transition shadow-sm">🤫 Silêncio</button>
                <button class="bg-gray-200 hover:bg-indigo-100 text-gray-700 px-4 py-2 rounded-full text-sm font-medium transition shadow-sm">📶 Wi-Fi Top</button>
            </div>

            <hr class="border-gray-200 mb-4">
            
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Recomendados perto de ti</h3>
            
            <div id="lista-spots" class="flex flex-col gap-3 pb-8">
                </div>
        </aside>

    </div>

       <div id="modal-detalhes" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[90] flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden relative">
            
            <div class="bg-white p-4 border-b border-gray-100 flex justify-between items-start">
                <div>
                    <h3 class="font-bold text-xl text-gray-800" id="detalhes-nome">Nome do Local</h3>
                    <span id="detalhes-tipo" class="text-[10px] text-indigo-600 bg-indigo-50 px-2 py-1 rounded font-bold uppercase border border-indigo-100 mt-1 inline-block">Tipo</span>
                </div>
                <button onclick="fecharDetalhes()" class="text-gray-400 hover:text-gray-800 font-bold text-2xl leading-none transition">&times;</button>
            </div>
            
            <div class="p-6">
                <p id="detalhes-descricao" class="text-sm text-gray-600 mb-6 border-l-4 border-indigo-400 pl-3 bg-gray-50 py-2 rounded-r">
                    A carregar descrição...
                </p>

                                <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Média da Comunidade</h4>
                <div class="grid grid-cols-2 gap-4 text-sm text-gray-700 bg-white p-4 rounded-lg border border-gray-200 mb-4 shadow-sm">
                    <div class="flex items-center gap-1"><span>🤫 Ruído:</span> <strong id="detalhes-ruido">0</strong></div>
                    <div class="flex items-center gap-1"><span>🔋 Tomadas:</span> <strong id="detalhes-tomadas">0</strong></div>
                    <div class="flex items-center gap-1"><span>📶 Wi-Fi:</span> <strong id="detalhes-wifi">0</strong></div>
                    <div class="flex items-center gap-1"><span>👥 Lotação:</span> <strong id="detalhes-lotacao">0</strong></div>
                </div>

                <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Comentários</h4>
                <div id="lista-comentarios" class="max-h-40 overflow-y-auto space-y-3 mb-6 pr-2 scrollbar-thin">
                    <p class="text-xs text-gray-400 italic">A carregar opiniões...</p>
                </div>

                <div class="border-t border-gray-100 pt-5 text-center">
                    <p class="text-xs text-gray-500 mb-3">Já estiveste aqui recentemente?</p>
                    <button id="btn-abrir-avaliacao" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition shadow-md">
                        Deixar a Minha Avaliação
                    </button>
                </div>
            </div>
        </div>
    </div>
    
// FORMULÁRIO
    <div id="modal-avaliacao" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden relative">
            
            <div class="bg-indigo-600 p-4 text-white flex justify-between items-center">
                <h3 class="font-bold text-lg">Avaliar: <span id="modal-nome-spot">Local</span></h3>
                <button onclick="fecharModal()" class="text-white hover:text-indigo-200 font-bold text-2xl leading-none">&times;</button>
            </div>
            
            <form id="form-review" class="p-6 space-y-4">
                <input type="hidden" id="modal-spot-id" name="spot_id">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">🤫 Ruído</label>
                        <select name="ruido" required class="w-full border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 p-2 text-sm">
                            <option value="">Escolhe...</option>
                            <option value="1">1 - Silêncio Absoluto</option>
                            <option value="2">2 - Muito Calmo</option>
                            <option value="3">3 - Normal</option>
                            <option value="4">4 - Barulhento</option>
                            <option value="5">5 - Impossível focar</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">👥 Lotação</label>
                        <select name="lotacao" required class="w-full border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 p-2 text-sm">
                            <option value="">Escolhe...</option>
                            <option value="1">1 - Vazio</option>
                            <option value="2">2 - Muito Espaço</option>
                            <option value="3">3 - Normal</option>
                            <option value="4">4 - Quase Cheio</option>
                            <option value="5">5 - Sem Lugares</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">🔋 Tomadas</label>
                        <select name="tomadas" required class="w-full border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 p-2 text-sm">
                            <option value="">Escolhe...</option>
                            <option value="1">1 - Nenhuma</option>
                            <option value="2">2 - Raras</option>
                            <option value="3">3 - Algumas</option>
                            <option value="4">4 - Muitas</option>
                            <option value="5">5 - Uma por mesa</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">📶 Wi-Fi</label>
                        <select name="wifi" required class="w-full border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 p-2 text-sm">
                            <option value="">Escolhe...</option>
                            <option value="1">1 - Sem net</option>
                            <option value="2">2 - Muito Lento</option>
                            <option value="3">3 - Normal</option>
                            <option value="4">4 - Rápido</option>
                            <option value="5">5 - Excelente</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Comentário (Opcional)</label>
                    <textarea name="comentario" rows="2" class="w-full border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 p-2 text-sm" placeholder="Como foi a tua experiência?"></textarea>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition mt-2 shadow-md">
                    Enviar Avaliação
                </button>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const utilizadorLogado = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>
    
    <script src="js/app.js?v=3"></script>
</body>
</html>