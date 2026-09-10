<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? null)) {
    header('Location: dashboard.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

$stmt = get_pdo()->prepare('SELECT photo FROM cars WHERE id = :id');
$stmt->execute(['id' => $id]);
$car = $stmt->fetch();

if ($car) {
    $stmt = get_pdo()->prepare('DELETE FROM cars WHERE id = :id');
    $stmt->execute(['id' => $id]);
    delete_photo($car['photo']);
}

header('Location: dashboard.php?msg=deleted');
exit;
