<?php
require_once 'db.php';

// Funções de Autenticação
function registerUser($name, $email, $password, $userType) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, ?)";
    $params = [$name, $email, $hashedPassword, $userType];
    
    $result = execute($sql, $params);
    return $result['success'] ? $result['insert_id'] : false;
}

function loginUser($email, $password) {
    $sql = "SELECT * FROM users WHERE email = ?";
    $user = fetchOne($sql, [$email]);
    
    if ($user && password_verify($password, $user['password'])) {
        // Atualizar último login
        $updateSql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
        execute($updateSql, [$user['id']]);
        
        // Definir dados da sessão
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['profile_picture'] = $user['profile_picture'];
        
        return true;
    }
    
    return false;
}

function logoutUser() {
    session_unset();
    session_destroy();
}

function getUserById($userId) {
    $sql = "SELECT * FROM users WHERE id = ?";
    return fetchOne($sql, [$userId]);
}

function getUserTypeLabel($userType) {
    switch ($userType) {
        case USER_STUDENT:
            return "Estudante";
        case USER_ALUMNI:
            return "Ex-aluno";
        case USER_PROFESSOR:
            return "Professor";
        case USER_ADMIN:
            return "Administrador";
        default:
            return "Usuário";
    }
}

// Funções de Posts
function createPost($userId, $title, $content) {
    $sql = "INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)";
    $params = [$userId, $title, $content];
    
    $result = execute($sql, $params);
    return $result['success'] ? $result['insert_id'] : false;
}

function getPosts($limit = 10, $offset = 0) {
    $sql = "SELECT p.*, u.name as author_name, u.user_type, u.profile_picture, 
                  (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
           FROM posts p
           JOIN users u ON p.user_id = u.id
           ORDER BY p.created_at DESC
           LIMIT ? OFFSET ?";
    
    return fetchAll($sql, [$limit, $offset]);
}

function getPostById($postId) {
    $sql = "SELECT p.*, u.name as author_name, u.user_type, u.profile_picture
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.id = ?";
    
    return fetchOne($sql, [$postId]);
}

// Funções de Comentários
function createComment($postId, $userId, $content) {
    $sql = "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)";
    $params = [$postId, $userId, $content];
    
    $result = execute($sql, $params);
    return $result['success'] ? $result['insert_id'] : false;
}

function getCommentsByPostId($postId) {
    $sql = "SELECT c.*, u.name as author_name, u.user_type, u.profile_picture
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.post_id = ?
            ORDER BY c.created_at ASC";
    
    return fetchAll($sql, [$postId]);
}

// Funções de formatação de data
function formatDate($date) {
    date_default_timezone_set('America/Sao_Paulo');
    $timestamp = strtotime($date);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) {
        return "Agora mesmo";
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ($minutes > 1 ? " minutos atrás" : " minuto atrás");
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ($hours > 1 ? " horas atrás" : " hora atrás");
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ($days > 1 ? " dias atrás" : " dia atrás");
    } else {
        return date('d/m/Y H:i', $timestamp);
    }
}

// Função para resumir conteúdo
function summarizeContent($content, $length = 200) {
    if (strlen($content) <= $length) {
        return $content;
    }
    
    $summary = substr($content, 0, $length);
    return $summary . "...";
}