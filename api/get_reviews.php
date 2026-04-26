<?php
// api/get_reviews.php
header('Content-Type: application/json; charset=utf-8');
require 'db_connect.php';

$spot_id = $_GET['spot_id'] ?? null;

if (!$spot_id) {
    echo json_encode(['erro' => 'ID do local não fornecido.']);
    exit;
}

try {
    // Vamos buscar o comentário, a data e o nome do utilizador
    $stmt = $pdo->prepare("
        SELECT r.comentario, r.data_review, u.nome as autor
        FROM reviews r
        JOIN utilizadores u ON r.user_id = u.id
        WHERE r.spot_id = ? AND r.comentario IS NOT NULL AND r.comentario != ''
        ORDER BY r.data_review DESC
    ");
    $stmt->execute([$spot_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($reviews);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()]);
}