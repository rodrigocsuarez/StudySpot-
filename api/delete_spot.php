<?php
// api/delete_spot.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require 'db_connect.php';

// Bloqueio de segurança: Impede execuções via URL direto (GET) ou utilizadores sem sessão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $spot_id = $_POST['id'] ?? null;
    $user_id = $_SESSION['user_id'];

    if (!$spot_id) {
        echo json_encode(['erro' => 'ID inválido.']);
        exit;
    }

    try {
        // --- 1. VERIFICAÇÃO(Segurança) ---
        // Antes de apagar seja o que for, garantimos que o spot pertence mesmo ao utilizador atual.
        $check = $pdo->prepare("SELECT id FROM spots WHERE id = ? AND criado_por = ?");
        $check->execute([$spot_id, $user_id]);
        
        if ($check->rowCount() === 0) {
             echo json_encode(['erro' => 'Não tens permissão ou o local não existe.']);
             exit;
        }

        // --- 2. ELIMINAÇÃO EM CASCATA (Manual via PHP) ---
        // Como o MySQL não tem o 'ON DELETE CASCADE' configurado na base de dados,
        // temos de limpar as dependências primeiro para evitar o Erro 1451 (Integridade).
        $delReviews = $pdo->prepare("DELETE FROM reviews WHERE spot_id = ?");
        $delReviews->execute([$spot_id]);

        // --- 3. ELIMINAÇÃO DO REGISTO PRINCIPAL ---
        // Agora que as reviews associadas foram apagadas, o spot não tem "filhos"
        // e o MySQL já permite eliminá-lo sem violar regras de integridade referencial.
        $stmt = $pdo->prepare("DELETE FROM spots WHERE id = ?");
        $stmt->execute([$spot_id]);

        echo json_encode(['sucesso' => true]);

    } catch(PDOException $e) {
        // Captura e devolve o erro limpo em JSON
        echo json_encode(['erro' => 'Erro interno: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['erro' => 'Acesso negado.']);
}