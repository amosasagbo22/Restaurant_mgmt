<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

$pdo = get_pdo();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(post('username', ''));
    $password = (string)post('password', '');
    $stmt = $pdo->prepare('SELECT id, password FROM users WHERE username = ? AND password = ?');
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['admin_id'] = (int)$user['id'];
        redirect('dashboard.php');
    } else {
        $error = 'Invalid credentials';
    }
}

include __DIR__ . '/../partials/header.php';
?>
<h2>Admin Login</h2>
<?php if ($error): ?><div class="alert error"><?php echo h($error); ?></div><?php endif; ?>
<form method="post">
    <div class="row">
        <div>
            <label>Username
                <input type="text" name="username" required>
            </label>
        </div>
        <div>
            <label>Password
                <input type="password" name="password" required>
            </label>
        </div>
    </div>
    <p><button type="submit">Login</button></p>
</form>
<?php include __DIR__ . '/../partials/footer.php'; ?>

