<?php
require_once '../includes/config.php';

// Verificar se o usuário está logado e é admin
if (!isLoggedIn() || $_SESSION['user_type'] != USER_ADMIN) {
    setMessage('Acesso negado. Apenas administradores podem acessar esta página.', 'error');
    redirect('pages/login.php');
}

// Processar filtros de período
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Se não especificado, usar últimos 30 dias
if (empty($startDate) || empty($endDate)) {
    $endDate = date('Y-m-d');
    $startDate = date('Y-m-d', strtotime('-30 days'));
}

// Validar datas
if (strtotime($startDate) > strtotime($endDate)) {
    $temp = $startDate;
    $startDate = $endDate;
    $endDate = $temp;
}

// Obter estatísticas
$stats = getGeneralStats($startDate, $endDate);
$activeUsers = getMostActiveUsers($startDate, $endDate, 10);
$postingFrequency = getPostingFrequency($startDate, $endDate);
$growthStats = getGrowthStats($startDate, $endDate);
$topPosts = getMostCommentedPosts($startDate, $endDate, 5);

// Preparar dados para o gráfico de linha
$chartLabels = [];
$chartData = [];
$dateRange = [];

// Criar array de todas as datas no período
$current = new DateTime($startDate);
$end = new DateTime($endDate);
$end->modify('+1 day'); // Incluir o último dia

while ($current < $end) {
    $dateRange[$current->format('Y-m-d')] = 0;
    $current->modify('+1 day');
}

// Preencher com dados reais
foreach ($postingFrequency as $freq) {
    $dateRange[$freq['date']] = (int)$freq['posts_count'];
}

// Preparar para JavaScript
foreach ($dateRange as $date => $count) {
    $chartLabels[] = date('d/m', strtotime($date));
    $chartData[] = $count;
}

// Preparar dados para o gráfico de pizza (distribuição de usuários)
$pieLabels = ['Estudantes', 'Ex-alunos', 'Professores', 'Administradores'];
$pieData = [$stats['students'], $stats['alumni'], $stats['professors'], $stats['admins']];
$pieColors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444']; // Azul, Verde, Amarelo, Vermelho

$pageTitle = 'Estatísticas - Painel Administrativo';
require_once '../includes/header.php';
?>

<style>
    /* Animações personalizadas */
@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes bounceIn {
    0% {
        opacity: 0;
        transform: scale(0.3);
    }
    50% {
        transform: scale(1.05);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes shimmer {
    0% {
        background-position: -200px 0;
    }
    100% {
        background-position: calc(200px + 100%) 0;
    }
}

/* Classes de utilidade para animações */
.animate-fadeInUp {
    animation: fadeInUp 0.6s ease-out forwards;
}

.animate-slideInLeft {
    animation: slideInLeft 0.6s ease-out forwards;
}

.animate-bounceIn {
    animation: bounceIn 0.8s ease-out forwards;
}

.animate-pulse-custom {
    animation: pulse 2s infinite;
}

/* Efeito shimmer para loading */
.shimmer {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200px 100%;
    animation: shimmer 1.5s infinite;
}

/* Efeitos de hover melhorados */
.card-hover {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.card-hover:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

/* Indicadores de ranking */
.ranking-badge {
    position: relative;
    overflow: hidden;
}

.ranking-badge::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(
        45deg,
        transparent,
        rgba(255, 255, 255, 0.3),
        transparent
    );
    transform: rotate(45deg);
    transition: all 0.5s;
    opacity: 0;
}

.ranking-badge:hover::before {
    animation: shimmer 0.8s ease-in-out;
    opacity: 1;
}

/* Efeitos para gráficos */
.chart-container {
    position: relative;
    transition: all 0.3s ease;
}

.chart-container:hover {
    transform: scale(1.02);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

/* Gradientes personalizados */
.gradient-gold {
    background: linear-gradient(135deg, #ffd700, #ffed4e, #fbbf24);
}

.gradient-silver {
    background: linear-gradient(135deg, #e5e7eb, #d1d5db, #9ca3af);
}

.gradient-bronze {
    background: linear-gradient(135deg, #cd7f32, #b45309, #92400e);
}

/* Efeitos de borda animada */
.border-animated {
    position: relative;
    background: linear-gradient(45deg, #f3f4f6, #ffffff);
    background-clip: padding-box;
    border: 2px solid transparent;
}

.border-animated::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: -1;
    margin: -2px;
    border-radius: inherit;
    background: linear-gradient(45deg, #3b82f6, #8b5cf6, #ef4444, #10b981);
    background-size: 400% 400%;
    animation: gradientShift 4s ease infinite;
}

@keyframes gradientShift {
    0%, 100% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
}

/* Efeitos de texto */
.text-glow {
    text-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
}

.text-glow-green {
    text-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
}

.text-glow-red {
    text-shadow: 0 0 10px rgba(239, 68, 68, 0.5);
}

.text-glow-yellow {
    text-shadow: 0 0 10px rgba(245, 158, 11, 0.5);
}

/* Efeitos de botão */
.btn-effect {
    position: relative;
    overflow: hidden;
    transform: translateZ(0);
}

.btn-effect::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.4),
        transparent
    );
    transition: left 0.5s;
}

.btn-effect:hover::before {
    left: 100%;
}

/* Responsividade melhorada */
@media (max-width: 768px) {
    .card-hover:hover {
        transform: translateY(-4px) scale(1.01);
    }
    
    .chart-container:hover {
        transform: scale(1.01);
    }
}

/* Efeitos de loading skeleton */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200px 100%;
    animation: shimmer 1.5s infinite linear;
}

.skeleton-text {
    height: 1rem;
    border-radius: 0.25rem;
    margin-bottom: 0.5rem;
}

.skeleton-avatar {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
}

/* Efeitos de transição suaves */
.smooth-transition {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Efeito de destaque para dados importantes */
.highlight-data {
    position: relative;
}

.highlight-data::after {
    content: '';
    position: absolute;
    top: -2px;
    right: -2px;
    bottom: -2px;
    left: -2px;
    background: linear-gradient(45deg, #3b82f6, #8b5cf6);
    border-radius: inherit;
    z-index: -1;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.highlight-data:hover::after {
    opacity: 1;
}

/* Dark mode support (se necessário) */
@media (prefers-color-scheme: dark) {
    .shimmer {
        background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
    }
    
    .skeleton {
        background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
    }
}
</style>

<div class="mb-8">
    <div class="text-center mb-6">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">
            <i class="fas fa-chart-bar mr-3 text-red-700"></i>Estatísticas da Plataforma
        </h1>
        <p class="text-gray-600 text-lg">Painel completo de métricas e análises do sistema</p>
    </div>
</div>

<!-- Filtro de Período -->
<div class="bg-gradient-to-r from-white to-gray-50 rounded-xl shadow-lg p-6 border border-gray-200 mb-8">
    <div class="flex items-center mb-4">
        <i class="fas fa-filter text-red-700 mr-2"></i>
        <h3 class="text-lg font-semibold text-gray-900">Filtros de Período</h3>
    </div>
    <form method="GET" class="flex flex-col md:flex-row gap-4 items-end">
        <div class="flex-1">
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-calendar-alt mr-1"></i>Data Inicial
            </label>
            <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition duration-300">
        </div>
        <div class="flex-1">
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-calendar-alt mr-1"></i>Data Final
            </label>
            <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition duration-300">
        </div>
        <div class="flex gap-3">
            <button type="submit" class="bg-red-700 hover:bg-red-800 text-white font-semibold py-3 px-6 rounded-lg transition duration-300 shadow-md hover:shadow-lg">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
            <a href="<?= SITE_URL ?>pages/admin/estatisticas.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-lg transition duration-300 shadow-md">
                <i class="fas fa-undo mr-2"></i>Limpar
            </a>
        </div>
    </form>
</div>

<!-- Período Selecionado -->
<div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6 mb-8 shadow-sm">
    <div class="flex items-center justify-center">
        <div class="bg-blue-100 p-3 rounded-full mr-4">
            <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
        </div>
        <div class="text-center">
            <span class="text-blue-900 font-bold text-lg block">
                Período Analisado
            </span>
            <span class="text-blue-700 text-sm">
                <?= date('d/m/Y', strtotime($startDate)) ?> até <?= date('d/m/Y', strtotime($endDate)) ?>
                (<?= abs((strtotime($endDate) - strtotime($startDate)) / 86400) + 1 ?> dias)
            </span>
        </div>
    </div>
</div>

<!-- Cards de Estatísticas Gerais -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <!-- Posts -->
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm font-medium">Posts Criados</p>
                <p class="text-3xl font-bold" data-animate-number><?= number_format($stats['posts']) ?></p>
                <?php if ($growthStats['growth']['posts'] != 0): ?>
                    <p class="text-blue-100 text-sm">
                        <i class="fas fa-arrow-<?= $growthStats['growth']['posts'] > 0 ? 'up' : 'down' ?>"></i>
                        <?= abs($growthStats['growth']['posts']) ?>% vs período anterior
                    </p>
                <?php endif; ?>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-full">
                <i class="fas fa-newspaper text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Comentários -->
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-green-100 text-sm font-medium">Comentários</p>
                <p class="text-3xl font-bold" data-animate-number><?= number_format($stats['comments']) ?></p>
                <?php if ($growthStats['growth']['comments'] != 0): ?>
                    <p class="text-green-100 text-sm">
                        <i class="fas fa-arrow-<?= $growthStats['growth']['comments'] > 0 ? 'up' : 'down' ?>"></i>
                        <?= abs($growthStats['growth']['comments']) ?>% vs período anterior
                    </p>
                <?php endif; ?>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-full">
                <i class="fas fa-comments text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Total de Usuários -->
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-purple-100 text-sm font-medium">Total de Usuários</p>
                <p class="text-3xl font-bold" data-animate-number><?= number_format($stats['total_users']) ?></p>
                <p class="text-purple-100 text-sm">Cadastrados no sistema</p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-full">
                <i class="fas fa-users text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Interações Totais -->
    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-red-100 text-sm font-medium">Total de Interações</p>
                <p class="text-3xl font-bold" data-animate-number><?= number_format($stats['posts'] + $stats['comments']) ?></p>
                <p class="text-red-100 text-sm">Posts + Comentários</p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-full">
                <i class="fas fa-chart-line text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Seção de Gráficos -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
    <!-- Gráfico de Frequência de Postagens -->
    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
        <div class="flex items-center mb-6">
            <div class="bg-red-100 p-2 rounded-lg mr-3">
                <i class="fas fa-chart-area text-red-700 text-xl"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-900">Frequência de Postagens</h2>
        </div>
        <div class="h-80">
            <canvas id="postingChart"></canvas>
        </div>
    </div>

    <!-- Gráfico de Pizza - Distribuição de Usuários -->
    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
        <div class="flex items-center mb-6">
            <div class="bg-purple-100 p-2 rounded-lg mr-3">
                <i class="fas fa-chart-pie text-purple-700 text-xl"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-900">Distribuição de Usuários</h2>
        </div>
        <div class="h-80">
            <canvas id="userDistributionChart"></canvas>
        </div>
    </div>
</div>

<!-- Distribuição Detalhada de Usuários por Tipo -->
<div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100 mb-10">
    <div class="flex items-center mb-8">
        <div class="bg-indigo-100 p-3 rounded-xl mr-4">
            <i class="fas fa-user-tag text-indigo-700 text-2xl"></i>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Comunidade Fatec Connect</h2>
            <p class="text-gray-600">Distribuição detalhada dos usuários cadastrados</p>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200 transform hover:scale-105 transition duration-300">
            <div class="bg-blue-500 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                <i class="fas fa-graduation-cap text-white text-3xl"></i>
            </div>
            <p class="text-3xl font-bold text-blue-900 mb-2" data-animate-number><?= number_format($stats['students']) ?></p>
            <p class="text-blue-700 font-semibold">Estudantes</p>
            <p class="text-blue-600 text-sm mt-1"><?= $stats['total_users'] > 0 ? round(($stats['students'] / $stats['total_users']) * 100, 1) : 0 ?>% do total</p>
        </div>
        <div class="text-center p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200 transform hover:scale-105 transition duration-300">
            <div class="bg-green-500 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                <i class="fas fa-user-graduate text-white text-3xl"></i>
            </div>
            <p class="text-3xl font-bold text-green-900 mb-2" data-animate-number><?= number_format($stats['alumni']) ?></p>
            <p class="text-green-700 font-semibold">Ex-alunos</p>
            <p class="text-green-600 text-sm mt-1"><?= $stats['total_users'] > 0 ? round(($stats['alumni'] / $stats['total_users']) * 100, 1) : 0 ?>% do total</p>
        </div>
        <div class="text-center p-6 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl border border-yellow-200 transform hover:scale-105 transition duration-300">
            <div class="bg-yellow-500 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                <i class="fas fa-chalkboard-teacher text-white text-3xl"></i>
            </div>
            <p class="text-3xl font-bold text-yellow-900 mb-2" data-animate-number><?= number_format($stats['professors']) ?></p>
            <p class="text-yellow-700 font-semibold">Professores</p>
            <p class="text-yellow-600 text-sm mt-1"><?= $stats['total_users'] > 0 ? round(($stats['professors'] / $stats['total_users']) * 100, 1) : 0 ?>% do total</p>
        </div>
        <div class="text-center p-6 bg-gradient-to-br from-red-50 to-red-100 rounded-xl border border-red-200 transform hover:scale-105 transition duration-300">
            <div class="bg-red-500 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                <i class="fas fa-user-shield text-white text-3xl"></i>
            </div>
            <p class="text-3xl font-bold text-red-900 mb-2" data-animate-number><?= number_format($stats['admins']) ?></p>
            <p class="text-red-700 font-semibold">Administradores</p>
            <p class="text-red-600 text-sm mt-1"><?= $stats['total_users'] > 0 ? round(($stats['admins'] / $stats['total_users']) * 100, 1) : 0 ?>% do total</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
    <!-- Posts Mais Comentados -->
    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
        <div class="flex items-center mb-6">
            <div class="bg-orange-100 p-2 rounded-lg mr-3">
                <i class="fas fa-fire text-orange-700 text-xl"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-900">Posts Mais Comentados</h2>
        </div>
        <?php if (empty($topPosts)): ?>
            <div class="text-center py-12 text-gray-500">
                <div class="bg-gray-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                </div>
                <p class="font-medium">Nenhum post encontrado</p>
                <p class="text-sm">no período selecionado</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($topPosts as $index => $post): ?>
                    <div class="flex items-center p-4 bg-gradient-to-r from-gray-50 to-white rounded-lg border border-gray-200 hover:shadow-md transition duration-300">
                        <div class="bg-gradient-to-r <?= $index == 0 ? 'from-gold-400 to-gold-600' : ($index == 1 ? 'from-gray-300 to-gray-500' : ($index == 2 ? 'from-orange-400 to-orange-600' : 'from-red-500 to-red-600')) ?> text-white font-bold text-sm w-10 h-10 rounded-full flex items-center justify-center mr-4 shadow-lg">
                            <?= $index + 1 ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 truncate text-lg"><?= htmlspecialchars($post['title']) ?></h3>
                            <div class="flex items-center text-sm text-gray-500 mt-1">
                                <i class="fas fa-user mr-1"></i>
                                <span class="mr-3"><?= htmlspecialchars($post['author_name']) ?></span>
                                <i class="fas fa-calendar mr-1"></i>
                                <span><?= formatDate($post['created_at']) ?></span>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="bg-blue-500 text-white font-bold text-lg px-4 py-2 rounded-lg shadow-md">
                                <?= $post['comments_count'] ?>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 font-medium">comentários</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Resumo de Engajamento -->
    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
        <div class="flex items-center mb-6">
            <div class="bg-teal-100 p-2 rounded-lg mr-3">
                <i class="fas fa-chart-bar text-teal-700 text-xl"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-900">Resumo de Engajamento</h2>
        </div>
        <div class="space-y-6">
            <div class="text-center p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg">
                <div class="text-3xl font-bold text-indigo-900"><?= $stats['posts'] > 0 ? round($stats['comments'] / $stats['posts'], 1) : 0 ?></div>
                <div class="text-indigo-700 font-medium">Comentários por Post</div>
            </div>
            <div class="text-center p-4 bg-gradient-to-r from-emerald-50 to-green-50 rounded-lg">
                <div class="text-3xl font-bold text-emerald-900"><?= number_format($stats['posts'] + $stats['comments']) ?></div>
                <div class="text-emerald-700 font-medium">Interações Totais</div>
            </div>
            <div class="text-center p-4 bg-gradient-to-r from-rose-50 to-pink-50 rounded-lg">
                <div class="text-3xl font-bold text-rose-900"><?= count($activeUsers) ?></div>
                <div class="text-rose-700 font-medium">Usuários Ativos</div>
            </div>
        </div>
    </div>
</div>

<!-- Usuários Mais Ativos -->
<div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100">
    <div class="flex items-center mb-8">
        <div class="bg-yellow-100 p-3 rounded-xl mr-4">
            <i class="fas fa-medal text-yellow-700 text-2xl"></i>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Top Usuários Mais Ativos</h2>
            <p class="text-gray-600">Ranking baseado em posts e comentários no período selecionado</p>
        </div>
    </div>
    
    <?php if (empty($activeUsers)): ?>
        <div class="text-center py-12 text-gray-500">
            <div class="bg-gray-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-slash text-gray-400 text-3xl"></i>
            </div>
            <p class="font-medium text-lg">Nenhuma atividade encontrada</p>
            <p class="text-sm">no período selecionado</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">Ranking</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">Usuário</th>
                        <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 uppercase tracking-wider">Posts</th>
                        <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 uppercase tracking-wider">Comentários</th>
                        <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($activeUsers as $index => $user): ?>
                        <tr class="hover:bg-gray-50 transition duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center justify-center">
                                    <?php if ($index < 3): ?>
                                        <div class="bg-gradient-to-r <?= $index == 0 ? 'from-yellow-400 to-yellow-600' : ($index == 1 ? 'from-gray-300 to-gray-500' : 'from-orange-400 to-orange-600') ?> text-white font-bold text-lg w-12 h-12 rounded-full flex items-center justify-center shadow-lg">
                                            <?= $index + 1 ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="bg-gray-200 text-gray-700 font-bold text-lg w-12 h-12 rounded-full flex items-center justify-center">
                                            <?= $index + 1 ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php if ($user['profile_picture']): ?>
                                        <img src="<?= SITE_URL . 'assets/images/' . $user['profile_picture'] ?>" alt="Perfil" class="w-12 h-12 rounded-full mr-4 border-2 border-gray-200">
                                    <?php else: ?>
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-r from-blue-400 to-purple-500 flex items-center justify-center mr-4">
                                            <i class="fas fa-user text-white text-lg"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="font-semibold text-gray-900 text-lg"><?= htmlspecialchars($user['name']) ?></div>
                                        <div class="text-sm text-gray-500 font-medium"><?= getUserTypeLabel($user['user_type']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="bg-blue-100 text-blue-800 text-sm font-semibold px-3 py-2 rounded-full">
                                    <?= $user['posts_count'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="bg-green-100 text-green-800 text-sm font-semibold px-3 py-2 rounded-full">
                                    <?= $user['comments_count'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="bg-red-100 text-red-800 text-lg font-bold px-4 py-2 rounded-full">
                                    <?= $user['total_interactions'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (count($activeUsers) == 10): ?>
            <div class="mt-6 text-center text-sm text-gray-500 bg-gray-50 p-3 rounded-lg">
                <i class="fas fa-info-circle mr-2"></i>
                Mostrando os 10 usuários mais ativos no período selecionado
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Scripts para os gráficos -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
// Dados dos gráficos
const chartLabels = <?= json_encode($chartLabels) ?>;
const chartData = <?= json_encode($chartData) ?>;
const pieLabels = <?= json_encode($pieLabels) ?>;
const pieData = <?= json_encode($pieData) ?>;
const pieColors = <?= json_encode($pieColors) ?>;

// Configuração do gráfico de linha (frequência de postagens)
const ctx = document.getElementById('postingChart').getContext('2d');
const postingChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: chartLabels,
        datasets: [{
            label: 'Posts por Dia',
            data: chartData,
            borderColor: '#dc2626',
            backgroundColor: 'rgba(220, 38, 38, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#dc2626',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: {
                display: true,
                text: 'Atividade de Postagens ao Longo do Tempo',
                font: {
                    size: 16,
                    weight: 'bold'
                },
                padding: 20
            },
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0,
                    font: {
                        size: 12
                    }
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                }
            },
            x: {
                ticks: {
                    font: {
                        size: 12
                    },
                    maxRotation: 45
                },
                grid: {
                    display: false
                }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        },
        elements: {
            point: {
                hoverBackgroundColor: '#dc2626',
                hoverBorderColor: '#ffffff'
            }
        }
    }
});

// Configuração do gráfico de pizza (distribuição de usuários)
const pieCtx = document.getElementById('userDistributionChart').getContext('2d');
const userDistributionChart = new Chart(pieCtx, {
    type: 'doughnut',
    data: {
        labels: pieLabels,
        datasets: [{
            data: pieData,
            backgroundColor: pieColors,
            borderColor: '#ffffff',
            borderWidth: 3,
            hoverBorderWidth: 4,
            hoverBackgroundColor: pieColors.map(color => color + 'DD')
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: {
                display: true,
                text: 'Composição da Comunidade',
                font: {
                    size: 16,
                    weight: 'bold'
                },
                padding: 20
            },
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    font: {
                        size: 13,
                        weight: '500'
                    },
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                        return `${label}: ${value} usuários (${percentage}%)`;
                    }
                },
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#ffffff',
                bodyColor: '#ffffff',
                borderColor: '#ffffff',
                borderWidth: 1,
                cornerRadius: 8,
                padding: 12
            }
        },
        cutout: '60%',
        animation: {
            animateRotate: true,
            duration: 1500,
            easing: 'easeOutQuart'
        },
        hover: {
            animationDuration: 300
        }
    }
});

// Animação dos números nos cards
function animateNumbers() {
    const elements = document.querySelectorAll('[data-animate-number]');
    
    elements.forEach(element => {
        const finalValue = parseInt(element.textContent.replace(/[^\d]/g, ''));
        const duration = 2000; // 2 segundos
        const steps = 60;
        const stepValue = finalValue / steps;
        const stepDuration = duration / steps;
        
        let currentValue = 0;
        let currentStep = 0;
        
        const timer = setInterval(() => {
            currentStep++;
            currentValue = Math.min(stepValue * currentStep, finalValue);
            
            // Formatação com separadores de milhares
            const formattedValue = Math.floor(currentValue).toLocaleString('pt-BR');
            element.textContent = formattedValue;
            
            if (currentStep >= steps) {
                clearInterval(timer);
                element.textContent = finalValue.toLocaleString('pt-BR');
            }
        }, stepDuration);
    });
}

// Animação de entrada dos cards
function animateCards() {
    const cards = document.querySelectorAll('.transform');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0) scale(1)';
                }, index * 100);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px) scale(0.95)';
        card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
        observer.observe(card);
    });
}

// Efeito de hover nos gráficos
function addChartHoverEffects() {
    const chartContainers = document.querySelectorAll('canvas');
    
    chartContainers.forEach(container => {
        container.addEventListener('mouseenter', () => {
            container.style.transform = 'scale(1.02)';
            container.style.transition = 'transform 0.3s ease';
        });
        
        container.addEventListener('mouseleave', () => {
            container.style.transform = 'scale(1)';
        });
    });
}

// Inicialização quando a página carrega
document.addEventListener('DOMContentLoaded', function() {
    // Delay para permitir que os gráficos sejam renderizados primeiro
    setTimeout(() => {
        animateNumbers();
        animateCards();
        addChartHoverEffects();
    }, 500);
});

// Função para atualizar os gráficos quando a janela é redimensionada
window.addEventListener('resize', function() {
    postingChart.resize();
    userDistributionChart.resize();
});

// Adicionar efeitos de loading para os gráficos
function showChartLoading(chartId) {
    const canvas = document.getElementById(chartId);
    const container = canvas.parentElement;
    
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'absolute inset-0 flex items-center justify-center bg-white bg-opacity-75';
    loadingDiv.innerHTML = `
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-600 mx-auto mb-4"></div>
            <p class="text-gray-600 font-medium">Carregando gráfico...</p>
        </div>
    `;
    
    container.style.position = 'relative';
    container.appendChild(loadingDiv);
    
    // Remover loading após 1 segundo
    setTimeout(() => {
        loadingDiv.remove();
    }, 1000);
}

// Função para adicionar tooltips personalizados
function addCustomTooltips() {
    const statCards = document.querySelectorAll('.bg-gradient-to-br');
    
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05) translateY(-5px)';
            this.style.boxShadow = '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) translateY(0)';
            this.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)';
        });
    });
}

// Adicionar efeito de pulso nos números importantes
function addPulseEffect() {
    const importantNumbers = document.querySelectorAll('[data-animate-number]');
    
    importantNumbers.forEach(number => {
        setInterval(() => {
            number.style.animation = 'pulse 2s ease-in-out';
            setTimeout(() => {
                number.style.animation = '';
            }, 2000);
        }, 10000); // Pulsar a cada 10 segundos
    });
}

// Função para destacar períodos de alta atividade no gráfico
function highlightPeakActivity() {
    if (chartData && chartData.length > 0) {
        const maxValue = Math.max(...chartData);
        const avgValue = chartData.reduce((a, b) => a + b, 0) / chartData.length;
        
        if (maxValue > avgValue * 1.5) {
            console.log('Período de alta atividade detectado!');
            // Adicionar indicador visual se necessário
        }
    }
}

// Inicializar todas as funcionalidades
setTimeout(() => {
    addCustomTooltips();
    addPulseEffect();
    highlightPeakActivity();
}, 1000);
</script>

<?php
include_once '../includes/footer.php';
?>
