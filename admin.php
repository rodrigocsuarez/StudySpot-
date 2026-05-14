<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php"); exit;
}
require 'api/db_connect.php';

// Estatísticas para o dashboard
$count_users = $pdo->query("SELECT COUNT(*) FROM utilizadores")->fetchColumn();
$count_spots = $pdo->query("SELECT COUNT(*) FROM spots WHERE status = 1")->fetchColumn();
$count_pending = $pdo->query("SELECT COUNT(*) FROM spots WHERE status = 0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>StudySpot | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-900">
    
    <nav class="bg-white shadow-sm p-4 mb-8">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold text-indigo-600">Backoffice StudySpot</h1>
            <a href="perfil.php" class="text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors">← Voltar ao Perfil</a>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto p-4">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-indigo-500">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Utilizadores</p>
                <p class="text-3xl font-bold text-gray-800"><?= $count_users ?></p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-green-500">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Spots Ativos</p>
                <p class="text-3xl font-bold text-gray-800"><?= $count_spots ?></p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-yellow-500">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Pendentes</p>
                <p class="text-3xl font-bold text-gray-800"><?= $count_pending ?></p>
            </div>
        </div>

        <div class="mb-8">
            <h2 class="font-bold text-2xl text-gray-800 mb-6">Fila de Aprovação</h2>
            
            <div id="lista-pendentes" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                </div>
        </div>
    </main>

    <script>
        async function carregarPendentes() {
            const res = await fetch('api/admin_get_pending.php');
            const spots = await res.json();
            const lista = document.getElementById('lista-pendentes');
            
            if(spots.length === 0) {
                lista.innerHTML = `
                    <div class="col-span-full bg-white p-12 rounded-2xl border border-dashed border-gray-300 text-center">
                        <p class="text-gray-400 italic">Tudo limpo! Não há novos locais para rever.</p>
                    </div>`;
                return;
            }

            lista.innerHTML = spots.map(s => {
                // Preparar a imagem (com fallback)
                const imgHTML = s.imagem_url 
                    ? `<img src="${s.imagem_url}" class="w-full h-48 object-cover rounded-t-2xl border-b border-gray-100">`
                    : `<div class="w-full h-48 bg-gray-100 flex items-center justify-center rounded-t-2xl border-b border-gray-200"><span class="text-gray-400 text-xs font-bold uppercase tracking-widest">Sem Imagem</span></div>`;

                return `
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 flex flex-col hover:shadow-lg transition-shadow">
                    ${imgHTML}
                    <div class="p-5 flex-1 flex flex-col">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="font-bold text-lg text-gray-800 line-clamp-1">${s.nome}</h3>
                            <span class="text-[10px] bg-yellow-100 text-yellow-700 px-2 py-1 rounded font-bold uppercase shrink-0">${s.tipo}</span>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-6 line-clamp-3 italic border-l-2 border-gray-100 pl-3">
                            "${s.descricao}"
                        </p>

                        <div class="mt-auto flex gap-3 pt-4 border-t border-gray-50">
                            <button onclick="decidir(${s.id}, 1)" class="flex-1 bg-green-50 text-green-700 hover:bg-green-500 hover:text-white py-3 rounded-xl text-sm font-bold transition-colors shadow-sm">
                                Aprovar
                            </button>
                            <button onclick="decidir(${s.id}, 2)" class="flex-1 bg-red-50 text-red-700 hover:bg-red-500 hover:text-white py-3 rounded-xl text-sm font-bold transition-colors shadow-sm">
                                Rejeitar
                            </button>
                        </div>
                    </div>
                </div>
                `;
            }).join('');
        }

        async function decidir(id, status) {
            const fd = new FormData();
            fd.append('id', id);
            fd.append('status', status);
            await fetch('api/admin_update_status.php', { method: 'POST', body: fd });
            location.reload(); // Atualiza a página para refrescar os contadores
        }

        carregarPendentes();
    </script>
</body>
</html>