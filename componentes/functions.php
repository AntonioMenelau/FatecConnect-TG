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


/// Funçoes para estatisticas


/**
 * Obtém estatísticas gerais do sistema por período
 */
function getGeneralStats($startDate = null, $endDate = null) {
    $whereClause = '';
    $params = [];
    
    if ($startDate && $endDate) {
        $whereClause = "WHERE DATE(created_at) BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
    }
    
    // Total de posts
    $sqlPosts = "SELECT COUNT(*) as total FROM posts $whereClause";
    $totalPosts = fetchOne($sqlPosts, $params)['total'];
    
    // Total de comentários
    $sqlComments = "SELECT COUNT(*) as total FROM comments $whereClause";
    $totalComments = fetchOne($sqlComments, $params)['total'];
    
    // Total de usuários por tipo (sem filtro de data para usuários)
    $sqlStudents = "SELECT COUNT(*) as total FROM users WHERE user_type = ?";
    $totalStudents = fetchOne($sqlStudents, [USER_STUDENT])['total'];
    
    $sqlAlumni = "SELECT COUNT(*) as total FROM users WHERE user_type = ?";
    $totalAlumni = fetchOne($sqlAlumni, [USER_ALUMNI])['total'];
    
    $sqlProfessors = "SELECT COUNT(*) as total FROM users WHERE user_type = ?";
    $totalProfessors = fetchOne($sqlProfessors, [USER_PROFESSOR])['total'];
    
    $sqlAdmins = "SELECT COUNT(*) as total FROM users WHERE user_type = ?";
    $totalAdmins = fetchOne($sqlAdmins, [USER_ADMIN])['total'];
    
    return [
        'posts' => $totalPosts,
        'comments' => $totalComments,
        'students' => $totalStudents,
        'alumni' => $totalAlumni,
        'professors' => $totalProfessors,
        'admins' => $totalAdmins,
        'total_users' => $totalStudents + $totalAlumni + $totalProfessors + $totalAdmins
    ];
}

/**
 * Obtém usuários com mais interações (posts + comentários)
 */
function getMostActiveUsers($startDate = null, $endDate = null, $limit = 10) {
    $whereClausePosts = '';
    $whereClauseComments = '';
    $params = [];
    
    if ($startDate && $endDate) {
        $whereClausePosts = "AND DATE(p.created_at) BETWEEN ? AND ?";
        $whereClauseComments = "AND DATE(c.created_at) BETWEEN ? AND ?";
        $params = [$startDate, $endDate, $startDate, $endDate];
    }
    
    $sql = "SELECT 
                u.id,
                u.name,
                u.email,
                u.user_type,
                u.profile_picture,
                COALESCE(posts_count, 0) as posts_count,
                COALESCE(comments_count, 0) as comments_count,
                (COALESCE(posts_count, 0) + COALESCE(comments_count, 0)) as total_interactions
            FROM users u
            LEFT JOIN (
                SELECT user_id, COUNT(*) as posts_count 
                FROM posts p 
                WHERE 1=1 $whereClausePosts
                GROUP BY user_id
            ) posts_stats ON u.id = posts_stats.user_id
            LEFT JOIN (
                SELECT user_id, COUNT(*) as comments_count 
                FROM comments c 
                WHERE 1=1 $whereClauseComments
                GROUP BY user_id
            ) comments_stats ON u.id = comments_stats.user_id
            WHERE u.user_type != ?
            ORDER BY total_interactions DESC, u.name ASC
            LIMIT ?";
    
    $params[] = USER_ADMIN; // Excluir admins da lista
    $params[] = $limit;
    
    return fetchAll($sql, $params);
}

/**
 * Obtém frequência de postagens por dia
 */
function getPostingFrequency($startDate = null, $endDate = null) {
    $whereClause = '';
    $params = [];
    
    if ($startDate && $endDate) {
        $whereClause = "WHERE DATE(created_at) BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
    } else {
        // Se não especificado, últimos 30 dias
        $whereClause = "WHERE DATE(created_at) >= DATE('now', '-30 days')";
    }
    
    $sql = "SELECT 
                DATE(created_at) as date,
                COUNT(*) as posts_count
            FROM posts 
            $whereClause
            GROUP BY DATE(created_at)
            ORDER BY date ASC";
    
    return fetchAll($sql, $params);
}

/**
 * Obtém estatísticas de crescimento (comparação com período anterior)
 */
function getGrowthStats($startDate, $endDate) {
    // Calcular duração do período
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $interval = $start->diff($end);
    $days = $interval->days;
    
    // Período anterior
    $previousStart = $start->sub(new DateInterval("P{$days}D"))->format('Y-m-d');
    $previousEnd = $startDate;
    
    // Estatísticas do período atual
    $currentStats = getGeneralStats($startDate, $endDate);
    
    // Estatísticas do período anterior
    $previousStats = getGeneralStats($previousStart, $previousEnd);
    
    return [
        'current' => $currentStats,
        'previous' => $previousStats,
        'growth' => [
            'posts' => $previousStats['posts'] > 0 ? 
                round((($currentStats['posts'] - $previousStats['posts']) / $previousStats['posts']) * 100, 1) : 
                ($currentStats['posts'] > 0 ? 100 : 0),
            'comments' => $previousStats['comments'] > 0 ? 
                round((($currentStats['comments'] - $previousStats['comments']) / $previousStats['comments']) * 100, 1) : 
                ($currentStats['comments'] > 0 ? 100 : 0)
        ]
    ];
}
?>

