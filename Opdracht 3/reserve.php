<?php
require_once __DIR__ . '/includes/bootstrap.php';

$id = (int) ($_GET['id'] ?? $_POST['car_id'] ?? 0);

$stmt = get_pdo()->prepare('SELECT * FROM cars WHERE id = :id');
$stmt->execute(['id' => $id]);
$car = $stmt->fetch();

if (!$car) {
    header('Location: index.php');
    exit;
}

$errors = [];
$formData = [
    'customer_name' => '',
    'email' => '',
    'phone' => '',
    'start_date' => $_GET['start_date'] ?? '',
    'end_date' => $_GET['end_date'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Ongeldige aanvraag. Vernieuw de pagina en probeer het opnieuw.';
    }

    $formData['customer_name'] = trim($_POST['customer_name'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['phone'] = trim($_POST['phone'] ?? '');
    $formData['start_date'] = trim($_POST['start_date'] ?? '');
    $formData['end_date'] = trim($_POST['end_date'] ?? '');

    if ($formData['customer_name'] === '') {
        $errors[] = 'Vul je naam in.';
    }
    if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Vul een geldig e-mailadres in.';
    }
    if ($formData['phone'] === '') {
        $errors[] = 'Vul je telefoonnummer in.';
    }

    $today = date('Y-m-d');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $formData['start_date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $formData['end_date'])) {
        $errors[] = 'Vul een geldige start- en einddatum in.';
    } elseif ($formData['start_date'] < $today) {
        $errors[] = 'De startdatum mag niet in het verleden liggen.';
    } elseif ($formData['end_date'] < $formData['start_date']) {
        $errors[] = 'De einddatum moet op of na de startdatum liggen.';
    }

    if (empty($errors) && !is_car_available_for_period(get_pdo(), $id, $formData['start_date'], $formData['end_date'])) {
        $errors[] = 'Deze auto is helaas niet beschikbaar in de gekozen periode. Kies een andere periode of auto.';
    }

    if (empty($errors)) {
        $days = (strtotime($formData['end_date']) - strtotime($formData['start_date'])) / 86400 + 1;
        $totalPrice = $days * (float) $car['price_per_day'];

        $stmt = get_pdo()->prepare(
            'INSERT INTO reservations (car_id, customer_name, email, phone, start_date, end_date, total_price)
             VALUES (:car_id, :customer_name, :email, :phone, :start_date, :end_date, :total_price)'
        );
        $stmt->execute([
            'car_id' => $id,
            'customer_name' => $formData['customer_name'],
            'email' => $formData['email'],
            'phone' => $formData['phone'],
            'start_date' => $formData['start_date'],
            'end_date' => $formData['end_date'],
            'total_price' => $totalPrice,
        ]);

        $reservation = [
            'customer_name' => $formData['customer_name'],
            'email' => $formData['email'],
            'phone' => $formData['phone'],
            'start_date' => $formData['start_date'],
            'end_date' => $formData['end_date'],
            'total_price' => $totalPrice,
        ];

        send_reservation_confirmation($reservation, $car);

        $_SESSION['reservation_confirmation'] = [
            'reservation' => $reservation,
            'car' => $car,
        ];

        header('Location: reservation_success.php');
        exit;
    }
}

$pageTitle = 'Reserveer ' . $car['brand'] . ' ' . $car['model'];
$assetBase = '';
require_once __DIR__ . '/includes/header.php';
?>
<p><a href="car.php?id=<?= (int) $car['id'] ?>">&larr; Terug naar auto</a></p>

<h1>Reserveer <?= e($car['brand']) ?> <?= e($car['model']) ?></h1>

<div class="summary-box">
    <?= e($car['brand']) ?> <?= e($car['model']) ?> (<?= e((string) $car['year']) ?>) &mdash; <?= format_price((float) $car['price_per_day']) ?> per dag
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?= implode('<br>', array_map('e', $errors)) ?>
    </div>
<?php endif; ?>

<div class="form-card">
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
        <input type="hidden" name="car_id" value="<?= (int) $car['id'] ?>">

        <div class="form-group">
            <label for="customer_name">Naam</label>
            <input type="text" id="customer_name" name="customer_name" required value="<?= e($formData['customer_name']) ?>">
        </div>
        <div class="form-group">
            <label for="email">E-mailadres</label>
            <input type="email" id="email" name="email" required value="<?= e($formData['email']) ?>">
        </div>
        <div class="form-group">
            <label for="phone">Telefoonnummer</label>
            <input type="tel" id="phone" name="phone" required value="<?= e($formData['phone']) ?>">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="start_date">Startdatum</label>
                <input type="date" id="start_date" name="start_date" required min="<?= date('Y-m-d') ?>" value="<?= e($formData['start_date']) ?>">
            </div>
            <div class="form-group">
                <label for="end_date">Einddatum</label>
                <input type="date" id="end_date" name="end_date" required min="<?= date('Y-m-d') ?>" value="<?= e($formData['end_date']) ?>">
            </div>
        </div>

        <button type="submit" class="btn">Reservering bevestigen</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
