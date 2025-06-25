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
