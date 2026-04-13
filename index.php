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

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="js/app.js"></script>
</body>
</html>