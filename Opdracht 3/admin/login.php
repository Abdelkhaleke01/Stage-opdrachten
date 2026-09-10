<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Ongeldige aanvraag. Probeer het opnieuw.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = get_pdo()->prepare('SELECT id, username, password_hash FROM admins WHERE username = :username');
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: dashboard.php');
            exit;
        }

        $error = 'Ongeldige gebruikersnaam of wachtwoord.';
    }
}

$pageTitle = 'Inloggen';
$csrfToken = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen - AutoReservering CMS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container">
        <a class="logo" href="../index.php">🚗 AutoReservering</a>
    </div>
</header>
<main>
    <div class="container login-wrapper">
        <div class="form-card" style="width: 100%;">
            <h1>Beheerder inloggen</h1>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <div class="form-group">
                    <label for="username">Gebruikersnaam</label>
                    <input type="text" id="username" name="username" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="password">Wachtwoord</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn">Inloggen</button>
            </form>
        </div>
    </div>
</main>
</body>
</html>
