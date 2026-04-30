<?php
// api/get_my_spots.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require 'db_connect.php';

// Segurança: Se não houver sessão, devolve lista vazia
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Seleciona todos os dados dos spots onde o criador é o user atual
    $stmt = $pdo->prepare("SELECT * FROM spots WHERE criado_por = ? ORDER BY id DESC");
    $stmt->execute([$user_id]);
    $meus_spots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Devolve o array para o JavaScript
    echo json_encode($meus_spots);

} catch(PDOException $e) {
    echo json_encode(['erro' => $e->getMessage()]);
}