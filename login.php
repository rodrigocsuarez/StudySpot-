<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudySpot - Autenticação</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center p-4">

    <div class="bg-white p-8 rounded-xl shadow-lg max-w-md w-full">
        <h1 class="text-3xl font-bold text-center text-indigo-600 mb-2">StudySpot</h1>
        <p class="text-center text-gray-500 mb-8">Entra ou cria a tua conta</p>

        <div id="form-registo" class="mb-6">
            <h2 class="text-xl font-semibold mb-4 border-b pb-2">Novo Utilizador</h2>
            <form action="api/auth.php" method="POST" class="space-y-4">
                <input type="hidden" name="acao" value="registar">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nome (Ex: Tiago)</label>
                    <input type="text" name="nome" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="senha" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition">Criar Conta</button>
            </form>
        </div>

        <div class="relative flex py-4 items-center">
            <div class="flex-grow border-t border-gray-300"></div>
            <span class="flex-shrink-0 mx-4 text-gray-400 text-sm">OU</span>
            <div class="flex-grow border-t border-gray-300"></div>
        </div>

        <div id="form-login">
            <h2 class="text-xl font-semibold mb-4 border-b pb-2">Já tenho conta</h2>
            <form action="api/auth.php" method="POST" class="space-y-4">
                <input type="hidden" name="acao" value="login">
                <input type="email" name="email" placeholder="Email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                <input type="password" name="senha" placeholder="Password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                <button type="submit" class="w-full bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded-lg transition">Entrar</button>
            </form>
        </div>

        <div class="mt-6 text-center">
            <a href="index.php" class="text-sm text-indigo-600 hover:underline">← Voltar ao Mapa</a>
        </div>
    </div>

</body>
</html>