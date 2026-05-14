<?php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

// Segurança
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    exit(json_encode(['erro' => 'Não autorizado']));
}


$stmt = $pdo->query("SELECT id, nome, tipo, descricao, imagem_url, criado_por FROM spots WHERE status = 0 ORDER BY id DESC");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));