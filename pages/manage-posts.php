<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Verifica se é admin
if (!isLoggedIn() || $_SESSION['user_type'] !== 4) {
    header('Location: ../index.php');
    exit;
}

// Busca todos os posts
$posts = fetchAll("
    SELECT p.*, u.name as author_name 
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC
");

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6">Gerenciamento de Posts</h2>
    
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Autor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($posts as $post): ?>
                <tr>
                    <td class="px-6 py-4">
                        <?= htmlspecialchars($post['title']) ?>
                    </td>
                    <td class="px-6 py-4">
                        <?= htmlspecialchars($post['author_name']) ?>
                    </td>
                    <td class="px-6 py-4">
                        <?= date('d/m/Y H:i', strtotime($post['created_at'])) ?>
                    </td>
                    <td class="px-6 py-4">
                        <a href="post.php?id=<?= $post['id'] ?>" class="text-blue-600 hover:text-blue-800 mr-3">
                            Ver
                        </a>
                        <button onclick="deletePost(<?= $post['id'] ?>)" class="text-red-600 hover:text-red-800">
                            Excluir
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function deletePost(postId) {
    if (confirm('Tem certeza que deseja excluir este post?')) {
        fetch(`../includes/delete-post.php?id=${postId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Erro ao excluir o post');
            }
        });
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>