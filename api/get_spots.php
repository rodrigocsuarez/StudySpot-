<?php
// api/get_spots.php
header('Content-Type: application/json; charset=utf-8');
require 'db_connect.php';

try {
    // Procuramos os spots e calculamos a média das avaliações de uma só vez
    $query = "
        SELECT 
            s.id, s.nome, s.tipo, s.lat, s.lng, s.descricao,
            COALESCE(AVG(r.nota_ruido), 0) as media_ruido,
            COALESCE(AVG(r.nota_wifi), 0) as media_wifi,
            COALESCE(AVG(r.nota_tomadas), 0) as media_tomadas,
            COALESCE(AVG(r.nota_lotacao), 0) as media_lotacao
        FROM spots s
        LEFT JOIN reviews r ON s.id = r.spot_id
        GROUP BY s.id
    ";
    
    $stmt = $pdo->query($query);
    $spots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($spots);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Falha na base de dados: ' . $e->getMessage()]);
}