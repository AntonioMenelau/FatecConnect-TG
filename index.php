<?php
require_once 'includes/config.php';

// Redirecionar para a timeline se o usuário já estiver logado
if (isLoggedIn()) {
    redirect('pages/timeline.php');
}

// Definir título da página
$pageTitle = 'Bem-vindo';

// Incluir cabeçalho
include 'includes/header.php';
?>

<div class="flex flex-col md:flex-row items-center justify-between gap-10 py-10">
    <div class="w-full md:w-1/2">
        <h1 class="text-4xl font-bold mb-4 text-red-700">Bem-vindo ao <span class="text-yellow-500">FatecConnect</span></h1>
        <p class="text-lg text-gray-700 mb-6">
            Uma plataforma exclusiva para conectar estudantes, ex-alunos e professores da Fatec.
            Compartilhe conhecimento, experiências e oportunidades com toda a comunidade acadêmica.
        </p>
        <div class="flex space-x-4">
            <a href="<?= SITE_URL ?>pages/login.php" class="bg-red-700 hover:bg-red-800 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-300">
                <i class="fas fa-sign-in-alt mr-2"></i> Login
            </a>
            <a href="<?= SITE_URL ?>pages/register.php" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-300">
                <i class="fas fa-user-plus mr-2"></i> Registrar
            </a>
        </div>
    </div>
    <div class="w-full md:w-1/2">
        <div class="bg-white rounded-xl shadow-xl p-6 border border-gray-200">
            <h2 class="text-2xl font-semibold mb-4 text-center text-gray-800">Por que se juntar ao FatecConnect?</h2>
            <ul class="space-y-4">
                <li class="flex items-start">
                    <div class="bg-red-100 p-3 rounded-full text-red-700 mr-4">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg text-gray-800">Comunidade Acadêmica</h3>
                        <p class="text-gray-600">Conecte-se com colegas de turma, ex-alunos e professores.</p>
                    </div>
                </li>
                <li class="flex items-start">
                    <div class="bg-yellow-100 p-3 rounded-full text-yellow-700 mr-4">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg text-gray-800">Compartilhe Conhecimento</h3>
                        <p class="text-gray-600">Troque experiências, materiais de estudo e dicas profissionais.</p>
                    </div>
                </li>
                <li class="flex items-start">
                    <div class="bg-blue-100 p-3 rounded-full text-blue-700 mr-4">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg text-gray-800">Oportunidades Profissionais</h3>
                        <p class="text-gray-600">Acesse ofertas de estágios, empregos e mentorias exclusivas.</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>