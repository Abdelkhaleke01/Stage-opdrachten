<?php
require_once __DIR__ . '/includes/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

$stmt = get_pdo()->prepare('SELECT * FROM cars WHERE id = :id');
$stmt->execute(['id' => $id]);
$car = $stmt->fetch();

if (!$car) {
    http_response_code(404);
    $pageTitle = 'Auto niet gevonden';
    $assetBase = '';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="empty-state">Deze auto bestaat niet (meer).</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$reserveUrl = 'reserve.php?id=' . $id;
if ($startDate !== '' && $endDate !== '') {
    $reserveUrl .= '&start_date=' . urlencode($startDate) . '&end_date=' . urlencode($endDate);
}

$pageTitle = $car['brand'] . ' ' . $car['model'];
$assetBase = '';
require_once __DIR__ . '/includes/header.php';
?>
<p><a href="index.php">&larr; Terug naar overzicht</a></p>

<div class="detail-grid">
    <div>
        <?php if ($car['photo']): ?>
            <img class="detail-photo" src="uploads/<?= e($car['photo']) ?>" alt="<?= e($car['brand'] . ' ' . $car['model']) ?>">
        <?php else: ?>
            <div class="detail-photo"></div>
        <?php endif; ?>
    </div>
    <div>
        <h1><?= e($car['brand']) ?> <?= e($car['model']) ?></h1>
        <p>
            <?php if ($car['available']): ?>
                <span class="badge badge-available">Beschikbaar</span>
            <?php else: ?>
                <span class="badge badge-unavailable">Niet beschikbaar</span>
            <?php endif; ?>
        </p>
        <p class="price" style="font-size:1.3rem;"><?= format_price((float) $car['price_per_day']) ?> / dag</p>
        <p><strong>Bouwjaar:</strong> <?= e((string) $car['year']) ?></p>
        <?php if ($car['description']): ?>
            <p><?= nl2br(e($car['description'])) ?></p>
        <?php endif; ?>

        <?php if ($car['available']): ?>
            <a class="btn" href="<?= e($reserveUrl) ?>">Reserveer deze auto</a>
        <?php else: ?>
            <p class="muted">Deze auto is momenteel niet beschikbaar voor reservering.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
