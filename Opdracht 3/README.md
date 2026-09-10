# Auto-reserveringssysteem

PHP + MySQL applicatie met twee onderdelen:

- **CMS** (`/admin`) — beveiligd met login, voor het beheren van auto's.
- **Reserveringspagina** (publieke site) — auto's bekijken en reserveren zonder in te loggen.

## Installatie (XAMPP)

1. Zet deze map in `C:\xampp\htdocs\` (bijvoorbeeld als `opdracht3`).
2. Start Apache en MySQL via het XAMPP Control Panel.
3. Importeer de database:
   - Open phpMyAdmin (`http://localhost/phpmyadmin`) en importeer `database/schema.sql`, of
   - via de command line: `mysql -u root < database/schema.sql`
4. Pas zo nodig de databasegegevens aan in `config.php` (standaard: host `localhost`, gebruiker `root`, geen wachtwoord).
5. Open de site in de browser: `http://localhost/opdracht3/`.

## Inloggen CMS

- URL: `http://localhost/opdracht3/admin/login.php`
- Gebruikersnaam: `admin`
- Wachtwoord: `Beheerder123!`

Verander dit wachtwoord voor productiegebruik (er is geen wachtwoord-wijzig-scherm; werk het `password_hash`-veld in de tabel `admins` bij met `password_hash()`).

## E-mailbevestiging

Reserveringen worden altijd bevestigd via `mail()`. Op een standaard lokale XAMPP-installatie is er meestal geen mailserver geconfigureerd, waardoor de e-mail niet echt wordt afgeleverd. Om dit toch controleerbaar te houden, wordt elke bevestigingsmail ook weggeschreven naar `logs/mail.log`, inclusief de volledige inhoud. Wil je echte e-mails versturen, configureer dan `sendmail_path` in `php.ini` (bijv. richting Mercury Mail, dat met XAMPP wordt meegeleverd) of vervang `send_reservation_confirmation()` in `includes/functions.php` door een SMTP-oplossing zoals PHPMailer.

## Mapstructuur

```
config.php              Database-, upload- en mailconfiguratie
includes/                Gedeelde PHP-logica en templates
database/schema.sql      Databasestructuur + voorbeelddata
css/style.css            Gedeelde stylesheet
uploads/                 Geüploade autofoto's
logs/mail.log            Log van verzonden bevestigingsmails
admin/                   CMS (login vereist)
index.php, car.php,      Publieke reserveringspagina
reserve.php,
reservation_success.php
```

## Beveiliging

- Wachtwoorden worden gehashed opgeslagen (`password_hash`/`password_verify`).
- Alle databasequery's gebruiken prepared statements (PDO).
- Alle formulieren (CMS én reservering) zijn beveiligd met een CSRF-token.
- Output wordt ge-escaped met `htmlspecialchars()`.
- Geüploade foto's worden gevalideerd op extensie, MIME-type en bestandsgrootte, en krijgen een willekeurige bestandsnaam.
- Verwijderen van een auto vraagt om bevestiging (JavaScript `confirm()`) en gebeurt alleen via een POST-request.
