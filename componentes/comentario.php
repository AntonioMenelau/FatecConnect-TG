<?php
require_once 'db.php';

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
