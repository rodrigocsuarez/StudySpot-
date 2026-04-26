<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require 'db_connect.php';

// Bloqueio de segurança
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['erro' => 'Sessão expirada.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $nome = $_POST['nome'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $lat = $_POST['lat'] ?? null;
    $lng = $_POST['lng'] ?? null;

    if (empty($nome) || empty($tipo) || empty($descricao) || !$lat || !$lng) {
        echo json_encode(['erro' => 'Preenche todos os campos e garante que clicaste no mapa.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO spots (nome, tipo, descricao, lat, lng, criado_por) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        // O $user_id vem da sessão, mas entra na coluna criado_por
        $stmt->execute([$nome, $tipo, $descricao, $lat, $lng, $user_id]);
        
        echo json_encode(['sucesso' => true]);

    } catch(PDOException $e) {
        echo json_encode(['erro' => 'Erro na BD: ' . $e->getMessage()]);
    }
}