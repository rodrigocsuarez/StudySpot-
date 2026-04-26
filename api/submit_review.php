<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require 'db_connect.php';

// Proteção 1: Só users logados podem submeter
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['erro' => 'Sessão expirada. Faz login novamente.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $spot_id = $_POST['spot_id'] ?? null;
    
    // Recolher as notas e o comentário
    $ruido = $_POST['ruido'] ?? null;
    $lotacao = $_POST['lotacao'] ?? null;
    $tomadas = $_POST['tomadas'] ?? null;
    $wifi = $_POST['wifi'] ?? null;
    $comentario = $_POST['comentario'] ?? null;

    // Proteção 2: Validar se os campos obrigatórios vieram
    if (!$spot_id || !$ruido || !$lotacao || !$tomadas || !$wifi) {
        echo json_encode(['erro' => 'Preenche todas as notas de 1 a 5.']);
        exit;
    }

    try {
        // Proteção 3: Evitar spam (1 avaliação por pessoa por local)
        $check = $pdo->prepare("SELECT id FROM reviews WHERE spot_id = ? AND user_id = ?");
        $check->execute([$spot_id, $user_id]);
        if ($check->rowCount() > 0) {
            echo json_encode(['erro' => 'Já avaliaste este local.']);
            exit;
        }

        // Inserção real na base de dados
        $stmt = $pdo->prepare("
            INSERT INTO reviews (spot_id, user_id, nota_ruido, nota_lotacao, nota_tomadas, nota_wifi, comentario) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$spot_id, $user_id, $ruido, $lotacao, $tomadas, $wifi, $comentario]);
        
        // Devolve o JSON de sucesso que o JavaScript está à espera
        echo json_encode(['sucesso' => true]);

    } catch(PDOException $e) {
        echo json_encode(['erro' => 'Erro na BD: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['erro' => 'Método HTTP inválido.']);
}