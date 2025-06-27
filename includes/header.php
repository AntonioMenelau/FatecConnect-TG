<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME ?></title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    typography: {
                        DEFAULT: {
                            css: {
                                maxWidth: 'none',
                                color: '#374151',
                                a: {
                                    color: '#b91c1c',
                                    '&:hover': {
                                        color: '#991b1b',
                                    },
                                },
                            },
                        },
                    },
                },
            },
            plugins: [
                require('@tailwindcss/typography'),
            ],
        }
    </script>
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="<?= SITE_URL ?>assets/images/logo.ico">
    <style>
        /* Estilos para o conteúdo do TinyMCE */
        .prose {
            max-width: none !important;
        }
        .prose h1 {
            font-size: 2em;
            margin-top: 0.67em;
            margin-bottom: 0.67em;
        }
        .prose h2 {
            font-size: 1.5em;
            margin-top: 0.83em;
            margin-bottom: 0.83em;
        }
        .prose h3 {
            font-size: 1.17em;
            margin-top: 1em;
            margin-bottom: 1em;
        }
        .prose ul {
            list-style-type: disc;
            margin-left: 2em;
            margin-top: 1em;
            margin-bottom: 1em;
        }
        .prose ol {
            list-style-type: decimal;
            margin-left: 2em;
            margin-top: 1em;
            margin-bottom: 1em;
        }
        .prose strong {
            font-weight: bold;
        }
        .prose em {
            font-style: italic;
        }
        .prose p {
            margin-top: 1em;
            margin-bottom: 1em;
        }
        .prose blockquote {
            margin-left: 2em;
            padding-left: 1em;
            border-left: 4px solid #e5e7eb;
        }
        .prose pre {
            background-color: #f3f4f6;
            padding: 1em;
            border-radius: 0.375rem;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="bg-red-700 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="<?= SITE_URL ?>" class="text-2xl font-bold">Fatec<span class="text-yellow-300">Connect</span></a>
            
            <?php if (isLoggedIn()): ?>
                <div class="flex items-center space-x-4">
                    <a href="<?= SITE_URL ?>pages/timeline.php" class="hover:text-yellow-300">
                        <i class="fas fa-home"></i> Timeline
                    </a>
                    <a href="<?= SITE_URL ?>pages/create-post.php" class="hover:text-yellow-300">
                        <i class="fas fa-plus-circle"></i> Novo Post
                    </a>
                    <div class="relative">
                        <button onclick="toggleDropdown()" class="flex items-center hover:text-yellow-300">
                            <?php if ($_SESSION['profile_picture']): ?>
                                <img src="<?= SITE_URL . 'assets/images/' . $_SESSION['profile_picture'] ?>" alt="Perfil" class="w-8 h-8 rounded-full mr-2">
                            <?php else: ?>
                                <i class="fas fa-user-circle text-2xl mr-2"></i>
                            <?php endif; ?>
                            <?= $_SESSION['user_name'] ?>
                            <i class="fas fa-caret-down ml-1"></i>
                        </button>
                        <div id="userDropdown" class="absolute right-0 w-48 mt-2 bg-white text-gray-800 rounded-md shadow-lg py-1 z-10 hidden">
                            <a href="<?= SITE_URL ?>pages/profile.php" class="block px-4 py-2 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i> Meu Perfil
                            </a>
                            <?php if (isLoggedIn() && $_SESSION['user_type'] === 4): ?>
                            <a href="<?= SITE_URL ?>pages/manage-posts.php" class="block px-4 py-2 hover:bg-gray-100">
                                <i class="fas fa-tasks mr-2"></i> Gerenciar Posts
                            </a>
                            <?php endif; ?>
                            <?php if (isLoggedIn() && $_SESSION['user_type'] === 4): ?>
                            <a href="<?= SITE_URL ?>pages/estatisticas.php" class="block px-4 py-2 hover:bg-gray-100">
                                <i class="fas fa-chart-bar"></i> Estatísticas
                            </a>
                            <?php endif; ?>
                            <a href="<?= SITE_URL ?>logout.php" class="block px-4 py-2 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i> Sair
                            </a>
                        </div>
                    </div>
                    <script>
                        function toggleDropdown() {
                            document.getElementById('userDropdown').classList.toggle('hidden');
                        }
                        // Close dropdown when clicking outside
                        window.onclick = function(event) {
                            if (!event.target.closest('.relative')) {
                                document.getElementById('userDropdown').classList.add('hidden');
                            }
                        }
                    </script>
                </div>
            <?php else: ?>
                <div class="flex items-center space-x-4">
                    <a href="<?= SITE_URL ?>pages/login.php" class="hover:text-yellow-300">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="<?= SITE_URL ?>pages/register.php" class="hover:text-yellow-300">
                        <i class="fas fa-user-plus"></i> Registrar
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Conteúdo Principal -->
    <main class="container mx-auto px-4 py-6 min-h-[calc(100vh-4rem)]">
        <?php displayMessage(); ?>