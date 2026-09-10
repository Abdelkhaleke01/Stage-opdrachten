<?php
require_once __DIR__ . '/includes/bootstrap.php';

$confirmation = $_SESSION['reservation_confirmation'] ?? null;
unset($_SESSION['reservation_confirmation']);

if (!$confirmation) {
    header('Location: index.php');
    exit;
}

$reservation = $confirmation['reservation'];
$car = $confirmation['car'];

$pageTitle = 'Reservering bevestigd';
$assetBase = '';
require_once __DIR__ . '/includes/header.php';
?>
<div class="form-card">
    <div class="alert alert-success">Je reservering is bevestigd! We hebben een bevestiging gestuurd naar <?= e($reservation['email']) ?>.</div>

    <h1>Reserveringsoverzicht</h1>
    <p><strong>Auto:</strong> <?= e($car['brand']) ?> <?= e($car['model']) ?> (<?= e((string) $car['year']) ?>)</p>
    <p><strong>Naam:</strong> <?= e($reservation['customer_name']) ?></p>
    <p><strong>E-mailadres:</strong> <?= e($reservation['email']) ?></p>
    <p><strong>Telefoonnummer:</strong> <?= e($reservation['phone']) ?></p>
    <p><strong>Periode:</strong> <?= e(format_date_nl($reservation['start_date'])) ?> t/m <?= e(format_date_nl($reservation['end_date'])) ?></p>
    <p><strong>Totaalprijs:</strong> <?= format_price((float) $reservation['total_price']) ?></p>

    <a class="btn" href="index.php">Terug naar overzicht</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
