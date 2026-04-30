<?php
// api/update_spot.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $spot_id = $_POST['id'] ?? null;
    $nome = $_POST['nome'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (!$spot_id || empty($nome) || empty($tipo) || empty($descricao)) {
        echo json_encode(['erro' => 'Dados incompletos.']);
        exit;
    }

    try {
        // --- SEGURANÇA: Atualiza APENAS se o criado_por for o user atual ---
        $stmt = $pdo->prepare("
            UPDATE spots 
            SET nome = ?, tipo = ?, descricao = ? 
            WHERE id = ? AND criado_por = ?
        ");
        $stmt->execute([$nome, $tipo, $descricao, $spot_id, $user_id]);

        // rowCount indica se alguma linha foi efetivamente alterada
        if ($stmt->rowCount() > 0) {
            echo json_encode(['sucesso' => true]);
        } else {
            // Pode não ter alterado porque os dados são iguais aos antigos, ou porque o user não é o dono
            echo json_encode(['sucesso' => true, 'nota' => 'Nenhuma alteração detetada ou sem permissão.']);
        }

    } catch(PDOException $e) {
        echo json_encode(['erro' => 'Erro na BD: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['erro' => 'Acesso negado.']);
}