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
