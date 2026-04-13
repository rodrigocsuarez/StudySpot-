<?php
// api/auth.php
session_start();
require 'db_connect.php'; // Confirma se o teu ficheiro se chama assim

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $acao = $_POST['acao'];

    // ==========================================
    // 1. LÓGICA DE REGISTO
    // ==========================================
    if ($acao === 'registar') {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $senha_limpa = $_POST['senha'];

        // Encriptar a password (Regra de ouro da engenharia de software)
        $senha_encriptada = password_hash($senha_limpa, PASSWORD_DEFAULT);

        try {
            // Preparar a query para evitar SQL Injection
            $stmt = $pdo->prepare("INSERT INTO utilizadores (nome, email, senha) VALUES (:nome, :email, :senha)");
            $stmt->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':senha' => $senha_encriptada
            ]);

            // Se o registo correu bem, fazemos o login automático e enviamos para o mapa
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_nome'] = $nome;
            $_SESSION['user_role'] = 'user'; // Por defeito

            header("Location: ../index.php");
            exit();

        } catch (PDOException $e) {
            // Se o email já existir, a base de dados vai disparar um erro
            die("Erro ao registar. O email já poderá estar em uso. <a href='../login.php'>Voltar</a>");
        }
    }

    // ==========================================
    // 2. LÓGICA DE LOGIN
    // ==========================================
    elseif ($acao === 'login') {
        $email = trim($_POST['email']);
        $senha_inserida = $_POST['senha'];

        try {
            // Ir buscar o utilizador pelo email
            $stmt = $pdo->prepare("SELECT id, nome, senha, role FROM utilizadores WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar se o utilizador existe E se a password encriptada bate certo
            if ($user && password_verify($senha_inserida, $user['senha'])) {
                
                // Sucesso! Guardar os dados na sessão ("bilhete de identidade" do browser)
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nome'] = $user['nome'];
                $_SESSION['user_role'] = $user['role'];

                header("Location: ../index.php");
                exit();
            } else {
                die("Email ou password incorretos. <a href='../login.php'>Tentar novamente</a>");
            }
        } catch (PDOException $e) {
            die("Erro no servidor: " . $e->getMessage());
        }
    }
}
?>