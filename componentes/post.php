<?php
require_once 'db.php';

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

/**
 * Busca posts pelo título (com paginação)
 */
function searchPostsByTitle($search, $limit = 10, $offset = 0) {
    $sql = "SELECT p.*, u.name as author_name, u.user_type, u.profile_picture, 
                   (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.title LIKE ?
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?";
    return fetchAll($sql, ["%$search%", $limit, $offset]);
}

/**
 * Obtém os posts mais recentes para moderação (sem filtro de status)
 */
function getRecentPostsForModeration($limit = 10) {
    $sql = "SELECT p.id, p.title, p.content, p.created_at, u.name as author_name, u.profile_picture
            FROM posts p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC
            LIMIT ?";
    return fetchAll($sql, [$limit]);
}

/**
 * Obtém posts mais comentados no período
 */
function getMostCommentedPosts($startDate = null, $endDate = null, $limit = 5) {
    $whereClause = '';
    $params = [];
    
    if ($startDate && $endDate) {
        $whereClause = "AND DATE(p.created_at) BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
    }
    
    $sql = "SELECT 
                p.id,
                p.title,
                p.created_at,
                u.name as author_name,
                u.user_type,
                COUNT(c.id) as comments_count
            FROM posts p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN comments c ON p.id = c.post_id
            WHERE 1=1 $whereClause
            GROUP BY p.id, p.title, p.created_at, u.name, u.user_type
            ORDER BY comments_count DESC, p.created_at DESC
            LIMIT ?";
    
    $params[] = $limit;
    
    return fetchAll($sql, $params);
}

