<?php
session_start();
// Se não estiver logado, chuta para o index
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <title>StudySpot | Meu Perfil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-900">

    <nav class="bg-white shadow-sm p-4">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <h1 class="font-bold text-xl text-indigo-600">StudySpot</h1>
            <a href="index.php" class="text-sm font-semibold text-gray-500 hover:text-indigo-600">← Voltar ao Mapa</a>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto p-6">
        <header class="mb-8">
            <h2 class="text-3xl font-bold">Olá, <?php echo $_SESSION['user_nome']; ?>!</h2>
            <p class="text-gray-500 text-sm">Aqui podes gerir os locais que adicionaste à comunidade.</p>
        </header>

        <section>
            <h3 class="text-lg font-bold mb-4 uppercase text-gray-400 tracking-wider">Os Meus Spots</h3>
            
            <div id="lista-meus-spots" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <p class="text-gray-400 italic">A carregar os teus locais...</p>
            </div>
        </section>
    </main>

    <script>
        // Função que carrega os spots via API
        async function carregarMeusSpots() {
            const container = document.getElementById('lista-meus-spots');
            
            try {
                const res = await fetch('api/get_my_spots.php');
                const spots = await res.json();

                if (spots.length === 0) {
                    container.innerHTML = '<div class="bg-white p-6 rounded-xl border border-dashed border-gray-300 text-center col-span-full"><p class="text-gray-400">Ainda não adicionaste nenhum local.</p></div>';
                    return;
                }

                container.innerHTML = ''; // Limpa o loading
                
                spots.forEach(spot => {
                    container.innerHTML += `
                        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-lg">${spot.nome}</h4>
                                    <span class="text-[10px] bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full uppercase font-bold">${spot.tipo}</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-4 line-clamp-2">${spot.descricao}</p>
                            </div>
                            
                            <div class="flex gap-2 pt-4 border-t border-gray-50">
                                <button onclick="editarSpot(${spot.id})" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold py-2 rounded-lg transition">
                                    Editar
                                </button>
                                <button onclick="confirmarEliminar(${spot.id})" class="flex-1 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold py-2 rounded-lg transition">
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    `;
                });
            } catch (e) {
                container.innerHTML = '<p class="text-red-500">Erro ao ligar ao servidor.</p>';
            }
        }

        // --- Lógica de Eliminar (D do CRUD) ---
        function confirmarEliminar(id) {
            if (confirm("Tens a certeza? Esta ação vai apagar o local e todas as avaliações associadas.")) {
                eliminarSpot(id);
            }
        }

        async function eliminarSpot(id) {
            try {
                // Criamos um FormData para enviar o ID
                const dados = new FormData();
                dados.append('id', id);

                const res = await fetch('api/delete_spot.php', {
                    method: 'POST',
                    body: dados
                });
                const result = await res.json();

                if (result.sucesso) {
                    carregarMeusSpots(); // Recarrega a lista sem refresh
                } else {
                    alert("Erro: " + result.erro);
                }
            } catch (e) {
                alert("Erro ao eliminar o spot.");
            }
        }

        // Inicializa a página
        carregarMeusSpots();
    </script>
</body>
</html>