<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? null)) {
    header('Location: dashboard.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : null;
$isEdit = $id !== null;

$errors = [];

$brand = trim($_POST['brand'] ?? '');
$model = trim($_POST['model'] ?? '');
$year = (int) ($_POST['year'] ?? 0);
$pricePerDay = (float) str_replace(',', '.', $_POST['price_per_day'] ?? '0');
$description = trim($_POST['description'] ?? '');
$available = ($_POST['available'] ?? '1') === '1' ? 1 : 0;

if ($brand === '' || $model === '') {
    $errors[] = 'Merk en model zijn verplicht.';
}
if ($year < 1950 || $year > (int) date('Y') + 1) {
    $errors[] = 'Voer een geldig bouwjaar in.';
}
if ($pricePerDay <= 0) {
    $errors[] = 'Voer een geldige prijs per dag in.';
}

$existingPhoto = null;
if ($isEdit) {
    $stmt = get_pdo()->prepare('SELECT photo FROM cars WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $existing = $stmt->fetch();

    if (!$existing) {
        header('Location: dashboard.php');
        exit;
    }
    $existingPhoto = $existing['photo'];
}

$newPhoto = null;
if (empty($errors)) {
    try {
        $newPhoto = handle_photo_upload($_FILES['photo'] ?? []);
    } catch (RuntimeException $exception) {
        $errors[] = $exception->getMessage();
    }
}

if (!empty($errors)) {
    // Bij validatiefouten sturen we de gebruiker terug naar het formulier.
    // Voor eenvoud tonen we de eerste foutmelding via de sessie.
    $_SESSION['form_errors'] = $errors;
    $redirectId = $isEdit ? '?id=' . $id : '';
    header('Location: car_form.php' . $redirectId);
    exit;
}

$photoToStore = $newPhoto ?? $existingPhoto;

if ($isEdit) {
    $stmt = get_pdo()->prepare(
        'UPDATE cars SET brand = :brand, model = :model, year = :year, price_per_day = :price, description = :description, available = :available, photo = :photo WHERE id = :id'
    );
    $stmt->execute([
        'brand' => $brand,
        'model' => $model,
        'year' => $year,
        'price' => $pricePerDay,
        'description' => $description,
        'available' => $available,
        'photo' => $photoToStore,
        'id' => $id,
    ]);

    if ($newPhoto !== null && $existingPhoto) {
        delete_photo($existingPhoto);
    }

    header('Location: dashboard.php?msg=updated');
    exit;
}

$stmt = get_pdo()->prepare(
    'INSERT INTO cars (brand, model, year, price_per_day, description, available, photo) VALUES (:brand, :model, :year, :price, :description, :available, :photo)'
);
$stmt->execute([
    'brand' => $brand,
    'model' => $model,
    'year' => $year,
    'price' => $pricePerDay,
    'description' => $description,
    'available' => $available,
    'photo' => $newPhoto,
]);

header('Location: dashboard.php?msg=added');
exit;
