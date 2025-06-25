<?php
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    if (empty($title) || empty($content)) {
        $error = "Título e conteúdo são obrigatórios";
    } else {
        $result = execute(
            "INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)",
            [$_SESSION['user_id'], $title, $content]
        );
        
        if ($result['success']) {
            $_SESSION['success_message'] = "Postagem criada com sucesso!";
            header('Location: timeline.php');
            exit;
        } else {
            $error = "Erro ao criar postagem. Tente novamente.";
        }
    }
}

require_once '../includes/header.php';
?>

<!-- Include TinyMCE -->
<script src="https://cdn.tiny.cloud/1/cyfhytt5jpq0unw4gubrsu98bp1dww1rr837ymtqfloxveiv/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#post-content',
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
        toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        height: 400,
        // Desabilitar telemetria
        send_usage_stats: false,
        // Corrigir problema do required
        setup: function(editor) {
            editor.on('change', function() {
                editor.save();
            });
        },
        // Melhorar segurança
        content_css: false,
        remove_script_host: true,
        convert_urls: true,
        // Permitir tags HTML específicas
        valid_elements: '*[*]', // Permite todos os elementos HTML
        extended_valid_elements: '*[*]', // Permite atributos extras
        verify_html: false, // Não remove HTML desconhecido
        cleanup: false, // Não limpa o HTML
    });
</script>

<div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6">Criar Nova Postagem</h2>
    
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <form method="post" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4" onsubmit="return validateForm()">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                Título
            </label>
            <input 
                type="text" 
                id="title" 
                name="title" 
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                required
            >
        </div>
        
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="post-content">
                Conteúdo
            </label>
            <textarea 
                id="post-content" 
                name="content" 
                required
            ><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
        </div>
        
        <div class="flex items-center justify-between">
            <button 
                type="submit" 
                class="bg-red-700 hover:bg-red-800 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
            >
                Publicar
            </button>
            <a 
                href="timeline.php" 
                class="inline-block align-baseline font-bold text-sm text-red-700 hover:text-red-800"
            >
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
function validateForm() {
    // Garantir que o conteúdo do TinyMCE seja válido
    var content = tinymce.get('post-content').getContent();
    if (!content) {
        alert('O conteúdo da postagem é obrigatório');
        return false;
    }
    return true;
}
</script>

<?php require_once '../includes/footer.php'; ?>