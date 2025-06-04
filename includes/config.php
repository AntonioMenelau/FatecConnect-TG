<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'fatecconnect');

// Configurações da aplicação
define('SITE_NAME', 'FatecConnect');
define('SITE_URL', 'https://fatecconnect.nxstech.com.br/');

// Tipos de usuário
define('USER_STUDENT', 1);
define('USER_ALUMNI', 2);
define('USER_PROFESSOR', 3);
define('USER_ADMIN', 4);

// Configurações de sessão
session_start();

// Função para verificar se usuário está logado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Função para redirecionar
function redirect($location) {
    header("Location: " . SITE_URL . $location);
    exit;
}

// Função para exibir mensagens
function setMessage($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function displayMessage() {
    if(isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'];
        
        echo "<div class='bg-" . ($type == 'success' ? 'green' : 'red') . "-100 border-l-4 border-" . 
             ($type == 'success' ? 'green' : 'red') . "-500 text-" . 
             ($type == 'success' ? 'green' : 'red') . "-700 p-4 mb-4' role='alert'>";
        echo "<p>" . $message . "</p>";
        echo "</div>";
        
        // Limpar a mensagem
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}