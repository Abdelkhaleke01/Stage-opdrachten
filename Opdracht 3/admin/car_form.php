<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_admin_login();

$car = [
    'id' => null,
    'brand' => '',
    'model' => '',
    'year' => date('Y'),
    'price_per_day' => '',
    'description' => '',
    'photo' => null,
    'available' => 1,
];

$isEdit = false;

if (isset($_GET['id'])) {
    $stmt = get_pdo()->prepare('SELECT * FROM cars WHERE id = :id');
    $stmt->execute(['id' => (int) $_GET['id']]);
    $found = $stmt->fetch();

    if (!$found) {
        header('Location: dashboard.php');
        exit;
    }

    $car = $found;
    $isEdit = true;
}

$formErrors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);

$pageTitle = $isEdit ? 'Auto bewerken' : 'Nieuwe auto toevoegen';
require_once __DIR__ . '/../includes/admin_header.php';
?>
<div class="page-header">
    <h1><?= e($pageTitle) ?></h1>
</div>

<?php if (!empty($formErrors)): ?>
    <div class="alert alert-error">
        <?= implode('<br>', array_map('e', $formErrors)) ?>
    </div>
<?php endif; ?>

<div class="form-card">
    <form method="post" action="car_save.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int) $car['id'] ?>">
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label for="brand">Merk</label>
                <input type="text" id="brand" name="brand" required value="<?= e($car['brand']) ?>">
            </div>
            <div class="form-group">
                <label for="model">Model</label>
                <input type="text" id="model" name="model" required value="<?= e($car['model']) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="year">Bouwjaar</label>
                <input type="number" id="year" name="year" required min="1950" max="<?= (int) date('Y') + 1 ?>" value="<?= e((string) $car['year']) ?>">
            </div>
            <div class="form-group">
                <label for="price_per_day">Prijs per dag (€)</label>
                <input type="number" id="price_per_day" name="price_per_day" required min="0" step="0.01" value="<?= e((string) $car['price_per_day']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="description">Beschrijving</label>
            <textarea id="description" name="description"><?= e($car['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Beschikbaarheid</label>
            <div class="radio-group">
                <label><input type="radio" name="available" value="1" <?= (int) $car['available'] === 1 ? 'checked' : '' ?>> Beschikbaar</label>
                <label><input type="radio" name="available" value="0" <?= (int) $car['available'] === 0 ? 'checked' : '' ?>> Niet beschikbaar</label>
            </div>
        </div>

        <div class="form-group">
            <label for="photo">Foto</label>
            <?php if ($car['photo']): ?>
                <p><img class="table-photo" style="width:120px;height:80px;" src="../uploads/<?= e($car['photo']) ?>" alt=""></p>
            <?php endif; ?>
            <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png,.webp">
            <p class="help-text">JPG, PNG of WEBP, max 5 MB.<?= $isEdit ? ' Laat leeg om de huidige foto te behouden.' : '' ?></p>
        </div>

        <button type="submit" class="btn"><?= $isEdit ? 'Wijzigingen opslaan' : 'Auto toevoegen' ?></button>
        <a href="dashboard.php" class="btn btn-secondary">Annuleren</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
