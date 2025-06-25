<?php
require_once 'db.php';

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

/**
 * Destaca o termo de busca no texto (case-insensitive)
 */
function highlightSearchTerm($text, $search) {
    if (empty($search)) return htmlspecialchars($text);
    $escapedSearch = preg_quote($search, '/');
    return preg_replace_callback(
        "/($escapedSearch)/i",
        function ($matches) {
            return '<span class="bg-yellow-200 font-bold">' . htmlspecialchars($matches[0]) . '</span>';
        },
        htmlspecialchars($text)
    );
}