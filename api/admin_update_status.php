<?php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    $id = $_POST['id'];
    $status = $_POST['status']; // 1 = Aprovar, 2 = Rejeitar

    $stmt = $pdo->prepare("UPDATE spots SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    echo json_encode(['sucesso' => true]);
}