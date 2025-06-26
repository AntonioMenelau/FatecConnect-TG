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

// Função para verificar se usuário está logado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Obtém estatísticas de atividade (posts e comentários) por tipo de usuário no período
 */
function getUserActivityStatsByType($startDate = null, $endDate = null) {
    $types = [
        'students' => USER_STUDENT,
        'alumni' => USER_ALUMNI,
        'professors' => USER_PROFESSOR,
        'admins' => USER_ADMIN
    ];
    $result = [];
    foreach ($types as $label => $type) {
        // Posts
        $sqlPosts = "SELECT COUNT(*) as total FROM posts WHERE user_id IN (SELECT id FROM users WHERE user_type = ?)";
        $paramsPosts = [$type];
        if ($startDate && $endDate) {
            $sqlPosts .= " AND DATE(created_at) BETWEEN ? AND ?";
            $paramsPosts[] = $startDate;
            $paramsPosts[] = $endDate;
        }
        $posts = fetchOne($sqlPosts, $paramsPosts)['total'];

        // Comentários
        $sqlComments = "SELECT COUNT(*) as total FROM comments WHERE user_id IN (SELECT id FROM users WHERE user_type = ?)";
        $paramsComments = [$type];
        if ($startDate && $endDate) {
            $sqlComments .= " AND DATE(created_at) BETWEEN ? AND ?";
            $paramsComments[] = $startDate;
            $paramsComments[] = $endDate;
        }
        $comments = fetchOne($sqlComments, $paramsComments)['total'];

        $result[$label] = [
            'posts' => $posts,
            'comments' => $comments
        ];
    }
    return $result;
}