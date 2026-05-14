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


    // LÓGICA DE UPLOAD DA IMAGEM
    $imagem_url = null; // Fica nulo por defeito

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        
        // Segurança: Só permite imagens
        if (in_array($extensao, $permitidas)) {
            // Gera um nome único para evitar sobreposição
            $novo_nome = uniqid('spot_', true) . '.' . $extensao;
            $destino = '../uploads/' . $novo_nome;
            
            // Cria a pasta automaticamente se não existir
            if (!is_dir('../uploads')) {
                mkdir('../uploads', 0777, true);
            }
            
            // Move da área temporária do PHP para a pasta final
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
                // Guarda apenas o caminho relativo à raiz do site
                $imagem_url = 'uploads/' . $novo_nome;
            }
        } else {
            echo json_encode(['erro' => 'Formato de imagem inválido. Usa JPG ou PNG.']);
            exit;
        }
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO spots (nome, tipo, descricao, lat, lng, criado_por, imagem_url) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nome, $tipo, $descricao, $lat, $lng, $user_id, $imagem_url]);
        
        echo json_encode(['sucesso' => true]);

    } catch(PDOException $e) {
        echo json_encode(['erro' => 'Erro na BD: ' . $e->getMessage()]);
    }
}