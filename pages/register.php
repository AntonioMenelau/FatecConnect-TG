<?php
require_once '../includes/config.php';

// Redirecionar se já estiver logado
if (isLoggedIn()) {
    redirect('pages/timeline.php');
}

// Processar formulário de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $userType = (int)($_POST['user_type'] ?? 0);
    
    // Validação básica
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        setMessage('Por favor, preencha todos os campos', 'error');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setMessage('E-mail inválido', 'error');
    } elseif (!preg_match('/^.+@fatec\.sp\.gov\.br$/', $email)) {
        setMessage('Use um e-mail institucional da Fatec', 'error');
    } elseif (strlen($password) < 6) {
        setMessage('A senha deve ter pelo menos 6 caracteres', 'error');
    } elseif ($password !== $confirmPassword) {
        setMessage('As senhas não coincidem', 'error');
    } elseif (!in_array($userType, [USER_STUDENT, USER_ALUMNI, USER_PROFESSOR])) {
        setMessage('Tipo de usuário inválido', 'error');
    } else {
        // Verificar se o e-mail já existe
        $sql = "SELECT id FROM users WHERE email = ?";
        $existingUser = fetchOne($sql, [$email]);
        
        if ($existingUser) {
            setMessage('Este e-mail já está cadastrado', 'error');
        } else {
            // Registrar usuário
            $userId = registerUser($name, $email, $password, $userType);
            
            if ($userId) {
                // Login automático após registro
                loginUser($email, $password);
                setMessage('Cadastro realizado com sucesso!', 'success');
                redirect('pages/timeline.php');
            } else {
                setMessage('Erro ao cadastrar. Tente novamente.', 'error');
            }
        }
    }
}

// Definir título da página
$pageTitle = 'Registro';

// Incluir cabeçalho
include '../includes/header.php';
?>

<div class="flex justify-center items-center py-8">
    <div class="w-full max-w-lg">
        <div class="bg-white rounded-lg shadow-lg p-8 border border-gray-200">
            <div class="text-center mb-6">
                <h2 class="text-3xl font-bold text-red-700">Registro <span class="text-yellow-500">FatecConnect</span></h2>
                <p class="text-gray-600 mt-2">Crie sua conta para se conectar com a comunidade Fatec</p>
            </div>
            
            <form action="<?= SITE_URL ?>pages/register.php" method="POST">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 font-medium mb-2">Nome Completo</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" 
                        placeholder="Seu nome completo"
                        value="<?= $_POST['name'] ?? '' ?>"
                        required
                    >
                </div>
                
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-medium mb-2">E-mail Institucional</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" 
                        placeholder="seu.email@fatec.sp.gov.br"
                        value="<?= $_POST['email'] ?? '' ?>"
                        required
                    >
                    <p class="text-sm text-gray-500 mt-1">Use seu e-mail institucional da Fatec</p>
                </div>
                
                <div class="mb-4">
                    <label for="user_type" class="block text-gray-700 font-medium mb-2">Você é:</label>
                    <select 
                        id="user_type" 
                        name="user_type" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                        required
                    >
                        <option value="">Selecione...</option>
                        <option value="<?= USER_STUDENT ?>" <?= (isset($_POST['user_type']) && $_POST['user_type'] == USER_STUDENT) ? 'selected' : '' ?>>
                            Estudante
                        </option>
                        <option value="<?= USER_ALUMNI ?>" <?= (isset($_POST['user_type']) && $_POST['user_type'] == USER_ALUMNI) ? 'selected' : '' ?>>
                            Ex-aluno
                        </option>
                        <option value="<?= USER_PROFESSOR ?>" <?= (isset($_POST['user_type']) && $_POST['user_type'] == USER_PROFESSOR) ? 'selected' : '' ?>>
                            Professor
                        </option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 font-medium mb-2">Senha</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" 
                        placeholder="Mínimo de 6 caracteres"
                        required
                    >
                </div>
                
                <div class="mb-6">
                    <label for="confirm_password" class="block text-gray-700 font-medium mb-2">Confirmar Senha</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" 
                        placeholder="Digite a senha novamente"
                        required
                    >
                </div>
                
                <button 
                    type="submit" 
                    class="w-full bg-red-700 hover:bg-red-800 text-white font-semibold py-3 px-4 rounded-lg shadow transition duration-300">
                    Criar Conta
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-600">Já tem uma conta? 
                    <a href="<?= SITE_URL ?>pages/login.php" class="text-red-700 hover:text-red-800 font-semibold">
                        Faça login
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>