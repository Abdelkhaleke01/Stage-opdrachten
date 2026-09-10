<?php
require_once __DIR__ . '/includes/bootstrap.php';

$search = trim($_GET['q'] ?? '');
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$year = $_GET['year'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

$sql = 'SELECT * FROM cars WHERE available = 1';
$params = [];

if ($search !== '') {
    $sql .= ' AND (brand LIKE :search1 OR model LIKE :search2)';
    $params['search1'] = '%' . $search . '%';
    $params['search2'] = '%' . $search . '%';
}

if ($minPrice !== '' && is_numeric($minPrice)) {
    $sql .= ' AND price_per_day >= :min_price';
    $params['min_price'] = (float) $minPrice;
}

if ($maxPrice !== '' && is_numeric($maxPrice)) {
    $sql .= ' AND price_per_day <= :max_price';
    $params['max_price'] = (float) $maxPrice;
}

if ($year !== '' && ctype_digit($year)) {
    $sql .= ' AND year = :year';
    $params['year'] = (int) $year;
}

$periodValid = $startDate !== '' && $endDate !== '' && $startDate <= $endDate;

if ($periodValid) {
    $sql .= ' AND id NOT IN (
        SELECT car_id FROM reservations
        WHERE start_date <= :end_date AND end_date >= :start_date
    )';
    $params['start_date'] = $startDate;
    $params['end_date'] = $endDate;
}

$sql .= ' ORDER BY brand, model';

$stmt = get_pdo()->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll();

$pageTitle = "Beschikbare auto's";
$assetBase = '';
require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header">
    <h1>Beschikbare auto's</h1>
</div>

<form method="get" class="filter-bar">
    <div class="form-group">
        <label for="q">Zoeken</label>
        <input type="text" id="q" name="q" value="<?= e($search) ?>" placeholder="Merk of model">
    </div>
    <div class="form-group">
        <label for="min_price">Prijs vanaf (€)</label>
        <input type="number" id="min_price" name="min_price" min="0" step="0.01" value="<?= e((string) $minPrice) ?>">
    </div>
    <div class="form-group">
        <label for="max_price">Prijs tot (€)</label>
        <input type="number" id="max_price" name="max_price" min="0" step="0.01" value="<?= e((string) $maxPrice) ?>">
    </div>
    <div class="form-group">
        <label for="year">Bouwjaar</label>
        <input type="number" id="year" name="year" min="1950" max="<?= (int) date('Y') + 1 ?>" value="<?= e((string) $year) ?>">
    </div>
    <div class="form-group">
        <label for="start_date">Ophaaldatum</label>
        <input type="date" id="start_date" name="start_date" value="<?= e($startDate) ?>">
    </div>
    <div class="form-group">
        <label for="end_date">Terugbrengdatum</label>
        <input type="date" id="end_date" name="end_date" value="<?= e($endDate) ?>">
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-secondary">Filteren</button>
        <a href="index.php" class="btn btn-secondary">Reset</a>
    </div>
</form>

<?php if ($startDate !== '' && $endDate !== '' && !$periodValid): ?>
    <div class="alert alert-error">De einddatum moet na de startdatum liggen.</div>
<?php endif; ?>

<?php if (empty($cars)): ?>
    <div class="empty-state">Geen auto's gevonden die aan je zoekopdracht voldoen.</div>
<?php else: ?>
    <div class="car-grid">
        <?php foreach ($cars as $car): ?>
            <a class="card" href="car.php?id=<?= (int) $car['id'] ?><?= $periodValid ? '&start_date=' . e($startDate) . '&end_date=' . e($endDate) : '' ?>" style="text-decoration:none;color:inherit;">
                <?php if ($car['photo']): ?>
                    <img class="card-photo" src="uploads/<?= e($car['photo']) ?>" alt="<?= e($car['brand'] . ' ' . $car['model']) ?>">
                <?php else: ?>
                    <div class="card-photo"></div>
                <?php endif; ?>
                <div class="card-body">
                    <h3><?= e($car['brand']) ?> <?= e($car['model']) ?></h3>
                    <span class="muted">Bouwjaar <?= e((string) $car['year']) ?></span>
                    <span class="price"><?= format_price((float) $car['price_per_day']) ?> / dag</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
