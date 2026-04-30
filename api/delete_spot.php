<?php
// api/delete_spot.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require 'db_connect.php';

// Só prossegue se houver ID e se o user estiver logado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $spot_id = $_POST['id'] ?? null;
    $user_id = $_SESSION['user_id'];

    try {
        // SEGURANÇA CRÍTICA: O spot só é apagado se o criado_por for o user atual
        // Isto impede que um utilizador apague o spot de outro via consola/URL
        $stmt = $pdo->prepare("DELETE FROM spots WHERE id = ? AND criado_por = ?");
        $stmt->execute([$spot_id, $user_id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['sucesso' => true]);
        } else {
            echo json_encode(['erro' => 'Não tens permissão ou o local não existe.']);
        }

    } catch(PDOException $e) {
        echo json_encode(['erro' => $e->getMessage()]);
    }
}