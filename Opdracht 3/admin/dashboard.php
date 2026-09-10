<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_admin_login();

$search = trim($_GET['q'] ?? '');
$availabilityFilter = $_GET['availability'] ?? '';

$sql = 'SELECT * FROM cars WHERE 1=1';
$params = [];

if ($search !== '') {
    $sql .= ' AND (brand LIKE :search1 OR model LIKE :search2)';
    $params['search1'] = '%' . $search . '%';
    $params['search2'] = '%' . $search . '%';
}

if ($availabilityFilter === 'available') {
    $sql .= ' AND available = 1';
} elseif ($availabilityFilter === 'unavailable') {
    $sql .= ' AND available = 0';
}

$sql .= ' ORDER BY created_at DESC';

$stmt = get_pdo()->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll();

$messages = [
    'added' => 'Auto is succesvol toegevoegd.',
    'updated' => 'Auto is succesvol bijgewerkt.',
    'deleted' => 'Auto is succesvol verwijderd.',
];
$flash = $messages[$_GET['msg'] ?? ''] ?? null;

$pageTitle = 'Auto-overzicht';
require_once __DIR__ . '/../includes/admin_header.php';
?>
<div class="page-header">
    <h1>Auto-overzicht</h1>
    <a class="btn" href="car_form.php">+ Nieuwe auto toevoegen</a>
</div>

<?php if ($flash): ?>
    <div class="alert alert-success"><?= e($flash) ?></div>
<?php endif; ?>

<form method="get" class="filter-bar">
    <div class="form-group">
        <label for="q">Zoeken (merk of model)</label>
        <input type="text" id="q" name="q" value="<?= e($search) ?>" placeholder="bv. Volkswagen">
    </div>
    <div class="form-group">
        <label for="availability">Beschikbaarheid</label>
        <select id="availability" name="availability">
            <option value="">Alle</option>
            <option value="available" <?= $availabilityFilter === 'available' ? 'selected' : '' ?>>Beschikbaar</option>
            <option value="unavailable" <?= $availabilityFilter === 'unavailable' ? 'selected' : '' ?>>Niet beschikbaar</option>
        </select>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-secondary">Filteren</button>
        <a href="dashboard.php" class="btn btn-secondary">Reset</a>
    </div>
</form>

<?php if (empty($cars)): ?>
    <div class="empty-state">Geen auto's gevonden.</div>
<?php else: ?>
    <table>
        <thead>
        <tr>
            <th>Foto</th>
            <th>Merk / model</th>
            <th>Bouwjaar</th>
            <th>Prijs per dag</th>
            <th>Status</th>
            <th>Acties</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($cars as $car): ?>
            <tr>
                <td>
                    <?php if ($car['photo']): ?>
                        <img class="table-photo" src="../uploads/<?= e($car['photo']) ?>" alt="">
                    <?php else: ?>
                        <div class="table-photo"></div>
                    <?php endif; ?>
                </td>
                <td><?= e($car['brand']) ?> <?= e($car['model']) ?></td>
                <td><?= e((string) $car['year']) ?></td>
                <td><?= format_price((float) $car['price_per_day']) ?></td>
                <td>
                    <?php if ($car['available']): ?>
                        <span class="badge badge-available">Beschikbaar</span>
                    <?php else: ?>
                        <span class="badge badge-unavailable">Niet beschikbaar</span>
                    <?php endif; ?>
                </td>
                <td class="actions">
                    <a class="btn btn-secondary btn-small" href="car_form.php?id=<?= (int) $car['id'] ?>">Bewerken</a>
                    <form method="post" action="car_delete.php" onsubmit="return confirm('Weet je zeker dat je deze auto wilt verwijderen? Dit kan niet ongedaan worden gemaakt.');">
                        <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= (int) $car['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-small">Verwijderen</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
