<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    setMessage('Você precisa estar logado para acessar esta página', 'error');
    redirect('pages/login.php');
}

// Verificar se o ID do post foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setMessage('Post não encontrado', 'error');
    redirect('pages/timeline.php');
}

$postId = (int)$_GET['id'];

// Buscar detalhes do post
$post = getPostById($postId);

// Verificar se o post existe
if (!$post) {
    setMessage('Post não encontrado', 'error');
    redirect('pages/timeline.php');
}

// Processar o envio de comentário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $content = trim($_POST['comment'] ?? '');
    
    if (empty($content)) {
        setMessage('O comentário não pode ser vazio', 'error');
    } else {
        $userId = $_SESSION['user_id'];
        $commentId = createComment($postId, $userId, $content);
        
        if ($commentId) {
            setMessage('Comentário adicionado com sucesso!', 'success');
        } else {
            setMessage('Erro ao adicionar comentário', 'error');
        }
        
        // Redirecionar para evitar reenvio do formulário
        redirect('pages/post.php?id=' . $postId);
    }
}

// Buscar comentários do post
$comments = getCommentsByPostId($postId);

// Definir título da página
$pageTitle = $post['title'];

// Incluir cabeçalho
include '../includes/header.php';
?>

<div class="mb-6">
    <a href="<?= SITE_URL ?>pages/timeline.php" class="text-red-700 hover:text-red-800 flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Voltar para Timeline
    </a>
</div>

<div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 mb-6">
    <!-- Cabeçalho do post -->
    <div class="flex items-center mb-4">
        <?php if ($post['profile_picture']): ?>
            <img src="<?= SITE_URL . 'assets/images/' . $post['profile_picture'] ?>" alt="Perfil" class="w-12 h-12 rounded-full mr-3">
        <?php else: ?>
            <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                <i class="fas fa-user-circle text-gray-400 text-2xl"></i>
            </div>
        <?php endif; ?>
        <div>
            <h3 class="font-bold text-gray-800"><?= htmlspecialchars($post['author_name']) ?></h3>
            <p class="text-sm text-gray-500">
                <?= getUserTypeLabel($post['user_type']) ?> • 
                <?= formatDate($post['created_at']) ?>
                <?php if ($post['updated_at'] && $post['updated_at'] !== $post['created_at']): ?>
                    • Editado em <?= formatDate($post['updated_at']) ?>
                <?php endif; ?>
            </p>
        </div>
    </div>
    
    <!-- Título e conteúdo do post -->
    <h1 class="text-2xl font-bold mb-4"><?= htmlspecialchars($post['title']) ?></h1>
    <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed mb-6">
        <?= $post['content'] // Remove htmlspecialchars para permitir HTML ?>
    </div>
    
    <!-- Separador -->
    <hr class="border-gray-200 my-6">
    
    <!-- Seção de comentários -->
    <div>
        <h2 class="text-xl font-bold mb-4">
            Comentários (<?= count($comments) ?>)
        </h2>
        
        <!-- Formulário de comentário -->
        <form action="<?= SITE_URL ?>pages/post.php?id=<?= $postId ?>" method="POST" class="mb-6">
            <div class="flex">
                <?php if ($_SESSION['profile_picture']): ?>
                    <img src="<?= SITE_URL . 'assets/images/' . $_SESSION['profile_picture'] ?>" alt="Perfil" class="w-10 h-10 rounded-full mr-3">
                <?php else: ?>
                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                        <i class="fas fa-user-circle text-gray-400 text-xl"></i>
                    </div>
                <?php endif; ?>
                <div class="flex-1">
                    <textarea 
                        name="comment" 
                        rows="3" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" 
                        placeholder="Escreva seu comentário..."
                        required
                    ></textarea>
                    <button 
                        type="submit" 
                        class="mt-2 bg-red-700 hover:bg-red-800 text-white font-semibold py-2 px-4 rounded-lg transition duration-300">
                        <i class="fas fa-paper-plane mr-2"></i> Comentar
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Lista de comentários -->
        <?php if (empty($comments)): ?>
            <div class="bg-gray-50 rounded-lg p-6 text-center">
                <p class="text-gray-500">Nenhum comentário ainda. Seja o primeiro a comentar!</p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($comments as $comment): ?>
                    <div class="flex bg-gray-50 rounded-lg p-4">
                        <?php if ($comment['profile_picture']): ?>
                            <img src="<?= SITE_URL . 'assets/images/' . $comment['profile_picture'] ?>" alt="Perfil" class="w-10 h-10 rounded-full mr-3">
                        <?php else: ?>
                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                <i class="fas fa-user-circle text-gray-400 text-xl"></i>
                            </div>
                        <?php endif; ?>
                        <div class="flex-1">
                            <div class="flex items-center mb-1">
                                <h4 class="font-bold text-gray-800 mr-2"><?= htmlspecialchars($comment['author_name']) ?></h4>
                                <span class="text-xs text-gray-500"><?= getUserTypeLabel($comment['user_type']) ?></span>
                            </div>
                            <p class="text-gray-700 mb-1"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                            <p class="text-xs text-gray-500"><?= formatDate($comment['created_at']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>