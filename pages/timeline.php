<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    setMessage('Você precisa estar logado para acessar esta página', 'error');
    redirect('pages/login.php');
}

// Parâmetro de busca
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Configuração de paginação
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Buscar posts para a timeline (com ou sem filtro de busca)
if (!empty($search)) {
    // Busca posts filtrados pelo título
    $posts = searchPostsByTitle($search, $limit, $offset);
    // Contar total de posts filtrados
    $sql = "SELECT COUNT(*) as total FROM posts WHERE title LIKE ?";
    $params = ["%$search%"];
    $result = fetchOne($sql, $params);
} else {
    // Busca todos os posts
    $posts = getPosts($limit, $offset);
    // Contar total de posts
    $sql = "SELECT COUNT(*) as total FROM posts";
    $result = fetchOne($sql);
}

$totalPosts = $result['total'];
$totalPages = ceil($totalPosts / $limit);

// Definir título da página
$pageTitle = 'Timeline';

// Incluir cabeçalho
include '../includes/header.php';
?>

<div class="flex flex-col md:flex-row gap-6">
    <!-- Sidebar com informações do usuário -->
    <div class="w-full md:w-1/4">
        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 sticky top-6">
            <div class="flex flex-col items-center mb-4">
                <?php if ($_SESSION['profile_picture']): ?>
                    <img src="<?= SITE_URL . 'assets/images/' . $_SESSION['profile_picture'] ?>" alt="Perfil" class="w-24 h-24 rounded-full mb-3">
                <?php else: ?>
                    <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center mb-3">
                        <i class="fas fa-user-circle text-gray-400 text-5xl"></i>
                    </div>
                <?php endif; ?>
                <h2 class="text-xl font-bold"><?= $_SESSION['user_name'] ?></h2>
                <p class="text-sm text-gray-500"><?= getUserTypeLabel($_SESSION['user_type']) ?></p>
            </div>
            
            <hr class="my-4">
            
            <a href="<?= SITE_URL ?>pages/create-post.php" class="block w-full bg-red-700 hover:bg-red-800 text-white text-center font-semibold py-2 px-4 rounded-lg mb-4 transition duration-300">
                <i class="fas fa-plus-circle mr-2"></i> Criar Novo Post
            </a>
            
            <a href="<?= SITE_URL ?>pages/profile.php" class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-center font-semibold py-2 px-4 rounded-lg transition duration-300">
                <i class="fas fa-user-edit mr-2"></i> Editar Perfil
            </a>
        </div>
    </div>
    
    <!-- Feed principal -->
    <div class="w-full md:w-3/4">
        <!-- Barra de pesquisa -->
        <div class="bg-white rounded-lg shadow-md p-4 border border-gray-200 mb-6">
            <form action="" method="GET" class="flex items-center">
                <div class="relative flex-grow">
                    <input 
                        type="text" 
                        name="search" 
                        value="<?= htmlspecialchars($search) ?>" 
                        placeholder="Pesquisar posts por título..." 
                        class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    >
                    <?php if (!empty($search)): ?>
                        <a href="<?= SITE_URL ?>pages/timeline.php" class="absolute inset-y-0 right-12 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <button type="submit" class="ml-2 bg-red-700 hover:bg-red-800 text-white font-semibold py-2 px-4 rounded-lg transition duration-300">
                    <i class="fas fa-search mr-2"></i> Buscar
                </button>
            </form>
        </div>

        <?php if (!empty($search)): ?>
            <div class="bg-gray-100 rounded-lg p-4 mb-6">
                <p class="text-gray-700">
                    <i class="fas fa-search mr-2"></i>
                    Exibindo resultados para: <strong><?= htmlspecialchars($search) ?></strong>
                    (<?= $totalPosts ?> <?= $totalPosts == 1 ? 'resultado' : 'resultados' ?>)
                </p>
            </div>
        <?php endif; ?>
        
        <?php if (empty($posts)): ?>
            <div class="bg-white rounded-lg shadow-md p-8 border border-gray-200 text-center">
                <i class="fas fa-newspaper text-gray-300 text-6xl mb-4"></i>
                <?php if (!empty($search)): ?>
                    <h2 class="text-2xl font-bold text-gray-700 mb-2">Nenhum resultado encontrado</h2>
                    <p class="text-gray-600 mb-4">Não encontramos posts com este título. Tente uma pesquisa diferente.</p>
                    <a href="<?= SITE_URL ?>pages/timeline.php" class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-lg transition duration-300 mr-2">
                        <i class="fas fa-arrow-left mr-2"></i> Voltar para Timeline
                    </a>
                <?php else: ?>
                    <h2 class="text-2xl font-bold text-gray-700 mb-2">Ainda não há posts</h2>
                    <p class="text-gray-600 mb-4">Seja o primeiro a compartilhar algo com a comunidade!</p>
                <?php endif; ?>
                <a href="<?= SITE_URL ?>pages/create-post.php" class="inline-block bg-red-700 hover:bg-red-800 text-white font-semibold py-2 px-6 rounded-lg transition duration-300">
                    <i class="fas fa-plus-circle mr-2"></i> Criar Post
                </a>
            </div>
        <?php else: ?>
            <!-- Posts -->
            <?php foreach ($posts as $post): ?>
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
                            </p>
                        </div>
                    </div>
                    
                    <!-- Título e conteúdo do post -->
                    <h1 class="text-2xl font-bold mb-4">
                        <?php if (!empty($search)): ?>
                            <?= highlightSearchTerm($post['title'], $search) ?>
                        <?php else: ?>
                            <?= htmlspecialchars($post['title']) ?>
                        <?php endif; ?>
                    </h1>
                    <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed mb-6">
                        <?php 
                        $summary = summarizeContent($post['content']);
                        echo $summary; // Content is sanitized in summarizeContent function
                        
                        if ($summary !== $post['content']):
                        ?>
                            <span class="block mt-2">
                                <a href="<?= SITE_URL ?>pages/post.php?id=<?= $post['id'] ?>" class="text-red-700 hover:text-red-800 font-medium">
                                    Ler mais...
                                </a>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Rodapé do post -->
                    <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                        <a href="<?= SITE_URL ?>pages/post.php?id=<?= $post['id'] ?>" class="text-gray-500 hover:text-red-700 transition duration-300">
                            <i class="far fa-comment mr-2"></i>
                            <?= $post['comment_count'] ?> Comentário<?= $post['comment_count'] != 1 ? 's' : '' ?>
                        </a>
                        <a href="<?= SITE_URL ?>pages/post.php?id=<?= $post['id'] ?>" class="text-gray-500 hover:text-red-700 transition duration-300">
                            <i class="far fa-eye mr-2"></i> Ver completo
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Paginação -->
            <?php if ($totalPages > 1): ?>
                <div class="flex justify-center mt-8">
                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-300">
                                <i class="fas fa-chevron-left mr-2"></i> Anterior
                            </a>
                        <?php endif; ?>
                        
                        <?php 
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $startPage + 4);
                        
                        for ($i = $startPage; $i <= $endPage; $i++): 
                        ?>
                            <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="px-4 py-2 <?= $i == $page ? 'bg-red-700 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?> rounded-lg transition duration-300">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-300">
                                Próxima <i class="fas fa-chevron-right ml-2"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>