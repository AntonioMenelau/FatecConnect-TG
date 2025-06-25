<?php
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$user = fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = trim($_POST['bio'] ?? '');
    $profile_picture = $user['profile_picture'];

    // Upload da nova foto de perfil
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $userId . '_' . time() . '.' . $ext;
        $dest = '../assets/images/' . $filename;
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $dest)) {
            $profile_picture = $filename;
            $_SESSION['profile_picture'] = $filename;
        }
    }

    execute(
        "UPDATE users SET bio = ?, profile_picture = ? WHERE id = ?",
        [$bio, $profile_picture, $userId]
    );
    $_SESSION['bio'] = $bio;
    header('Location: profile.php?success=1');
    exit;
}

require_once '../includes/header.php';
?>

<h2 class="text-2xl font-bold mb-4">Meu Perfil</h2>
<div class="bg-white rounded-lg shadow p-6 max-w-lg mx-auto">
    <form method="post" enctype="multipart/form-data">
        <div class="flex items-center mb-4">
            <?php if ($user['profile_picture']): ?>
                <img src="../assets/images/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Foto de Perfil" class="w-20 h-20 rounded-full mr-4">
            <?php else: ?>
                <i class="fas fa-user-circle text-6xl text-gray-400 mr-4"></i>
            <?php endif; ?>
            <div>
                <label class="block font-semibold">Alterar foto:</label>
                <input type="file" name="profile_picture" accept="image/*" class="mt-1">
            </div>
        </div>
        <div class="mb-4">
            <label class="block font-semibold">Nome:</label>
            <input type="text" value="<?= htmlspecialchars($user['name']) ?>" class="w-full border rounded px-3 py-2 bg-gray-100" disabled>
        </div>
        <div class="mb-4">
            <label class="block font-semibold">E-mail:</label>
            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full border rounded px-3 py-2 bg-gray-100" disabled>
        </div>
        <div class="mb-4">
            <label class="block font-semibold">Bio:</label>
            <textarea name="bio" class="w-full border rounded px-3 py-2" rows="3"><?= htmlspecialchars($user['bio']) ?></textarea>
        </div>
        <button type="submit" class="bg-red-700 text-white px-4 py-2 rounded hover:bg-red-800">Salvar Alterações</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>