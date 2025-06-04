<?php
require_once 'db.php';
require_once 'functions.php';

session_start();

// Verifica se é admin
if (!isLoggedIn() || $_SESSION['user_type'] !== 4) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$postId = $_GET['id'] ?? null;

if (!$postId) {
    echo json_encode(['success' => false, 'message' => 'ID do post não fornecido']);
    exit;
}

$result = execute("DELETE FROM posts WHERE id = ?", [$postId]);

echo json_encode(['success' => $result['success']]);