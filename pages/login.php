<?php
require_once '../includes/config.php';

// Redirecionar se já estiver logado
if (isLoggedIn()) {
    redirect('pages/timeline.php');
}

// Processar formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validação básica
    if (empty($email) || empty($password)) {
        setMessage('Por favor, preencha todos os campos', 'error');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setMessage('E-mail inválido', 'error');
    } else {
        // Tentar login
        if (loginUser($email, $password)) {
            redirect('pages/timeline.php');
        } else {
            setMessage('E-mail ou senha incorretos', 'error');
        }
    }
}

// Definir título da página
$pageTitle = 'Login';

// Incluir cabeçalho
include '../includes/header.php';
?>

<div class="flex justify-center items-center py-8">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-lg p-8 border border-gray-200">
            <div class="text-center mb-6">
                <h2 class="text-3xl font-bold text-red-700">Login <span class="text-yellow-500">FatecConnect</span></h2>
                <p class="text-gray-600 mt-2">Entre com suas credenciais para acessar</p>
            </div>
            
            <form action="<?= SITE_URL ?>pages/login.php" method="POST">
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-medium mb-2">E-mail</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" 
                        placeholder="seu.email@fatec.sp.gov.br"
                        required
                    >
                </div>
                
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 font-medium mb-2">Senha</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" 
                        placeholder="Sua senha"
                        required
                    >
                    <a href="#" class="text-sm text-red-700 hover:text-red-800 mt-2 inline-block">Esqueceu sua senha?</a>
                </div>
                
                <button 
                    type="submit" 
                    class="w-full bg-red-700 hover:bg-red-800 text-white font-medium py-2 px-4 rounded-lg transition duration-300"
                >
                    Entrar
                </button>
                
                <div class="text-center mt-4">
                    <p class="text-gray-600">
                        Não tem uma conta? 
                        <a href="register.php" class="text-red-700 hover:text-red-800 font-medium">
                            Registre-se
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>