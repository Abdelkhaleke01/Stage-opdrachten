<?php

/**
 * Veilig HTML-escapen van output.
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function generate_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool
{
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function format_price(float $price): string
{
    return '€ ' . number_format($price, 2, ',', '.');
}

function format_date_nl(string $date): string
{
    $timestamp = strtotime($date);
    return $timestamp ? date('d-m-Y', $timestamp) : $date;
}

/**
 * Controleert of een auto beschikbaar is (beheerderstatus) en geen overlappende
 * reservering heeft in de opgegeven periode.
 */
function is_car_available_for_period(PDO $pdo, int $carId, string $startDate, string $endDate, ?int $excludeReservationId = null): bool
{
    $stmt = $pdo->prepare('SELECT available FROM cars WHERE id = :id');
    $stmt->execute(['id' => $carId]);
    $car = $stmt->fetch();

    if (!$car || (int) $car['available'] !== 1) {
        return false;
    }

    $sql = 'SELECT COUNT(*) FROM reservations
            WHERE car_id = :car_id
              AND start_date <= :end_date
              AND end_date >= :start_date';
    $params = [
        'car_id' => $carId,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ];

    if ($excludeReservationId !== null) {
        $sql .= ' AND id != :exclude_id';
        $params['exclude_id'] = $excludeReservationId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return (int) $stmt->fetchColumn() === 0;
}

/**
 * Verwerkt een geüploade autofoto. Geeft de opgeslagen bestandsnaam terug,
 * of null als er geen (nieuw) bestand is geüpload.
 * Gooit een RuntimeException met een gebruiksvriendelijke boodschap bij een ongeldig bestand.
 */
function handle_photo_upload(array $file): ?string
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Er ging iets mis bij het uploaden van de foto.');
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        throw new RuntimeException('De foto is te groot (max 5 MB).');
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_PHOTO_TYPES, true)) {
        throw new RuntimeException('Ongeldig bestandstype. Toegestaan: jpg, jpeg, png, webp.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mimeType, $allowedMimeTypes, true)) {
        throw new RuntimeException('Het bestand is geen geldige afbeelding.');
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    $destination = UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('De foto kon niet worden opgeslagen.');
    }

    return $filename;
}

function delete_photo(?string $filename): void
{
    if ($filename === null || $filename === '') {
        return;
    }

    $path = UPLOAD_DIR . $filename;
    if (is_file($path)) {
        unlink($path);
    }
}

/**
 * Verstuurt een bevestigingsmail. Als de lokale mailserver niet is ingesteld,
 * wordt de inhoud altijd weggeschreven naar logs/mail.log zodat de reservering
 * ook dan controleerbaar is.
 */
function send_reservation_confirmation(array $reservation, array $car): bool
{
    $subject = 'Bevestiging van je reservering - ' . $car['brand'] . ' ' . $car['model'];

    $body = "Beste {$reservation['customer_name']},\n\n"
        . "Je reservering is bevestigd. Hieronder vind je de details:\n\n"
        . "Auto: {$car['brand']} {$car['model']} ({$car['year']})\n"
        . "Periode: " . format_date_nl($reservation['start_date']) . " t/m " . format_date_nl($reservation['end_date']) . "\n"
        . "Totaalprijs: " . format_price((float) $reservation['total_price']) . "\n\n"
        . "Bedankt voor je reservering!\n"
        . MAIL_FROM_NAME . "\n";

    $headers = 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM . '>' . "\r\n"
        . 'Content-Type: text/plain; charset=utf-8';

    $sent = @mail($reservation['email'], $subject, $body, $headers);

    $logDir = dirname(MAIL_LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logEntry = sprintf(
        "[%s] Aan: %s | Verzonden: %s\nOnderwerp: %s\n%s\n%s\n",
        date('Y-m-d H:i:s'),
        $reservation['email'],
        $sent ? 'ja' : 'nee (mailserver niet beschikbaar, zie inhoud hieronder)',
        $subject,
        $body,
        str_repeat('-', 60)
    );
    file_put_contents(MAIL_LOG_FILE, $logEntry, FILE_APPEND);

    return true;
}
