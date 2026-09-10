-- Auto-reserveringssysteem database schema
-- Importeren: mysql -u root < schema.sql  (of via phpMyAdmin)

CREATE DATABASE IF NOT EXISTS auto_reservering CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE auto_reservering;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    year INT NOT NULL,
    price_per_day DECIMAL(10,2) NOT NULL,
    description TEXT,
    photo VARCHAR(255) DEFAULT NULL,
    available TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    car_id INT NOT NULL,
    customer_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reservations_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    INDEX idx_reservations_car_dates (car_id, start_date, end_date)
) ENGINE=InnoDB;

-- Standaard beheerder: gebruikersnaam "admin", wachtwoord "Beheerder123!"
-- Verander dit wachtwoord na de eerste keer inloggen.
INSERT INTO admins (username, password_hash) VALUES
    ('admin', '$2y$10$yVLH6uM.mnBEQFBnOH2jK.h99pOjiElngwdTUArMf5DRjGpeeGlyy')
    ON DUPLICATE KEY UPDATE username = username;

-- Voorbeeldauto's (let op: dit blok nogmaals uitvoeren voegt de auto's opnieuw toe)
INSERT INTO cars (brand, model, year, price_per_day, description, photo, available) VALUES
    ('Volkswagen', 'Golf', 2021, 45.00, 'Compacte en zuinige hatchback, ideaal voor de stad en korte ritten.', NULL, 1),
    ('Tesla', 'Model 3', 2023, 89.00, 'Volledig elektrische sedan met groot bereik en autopilot.', NULL, 1),
    ('BMW', 'X5', 2020, 95.00, 'Ruime SUV met veel comfort, geschikt voor lange reizen en gezinnen.', NULL, 1),
    ('Fiat', '500', 2019, 35.00, 'Stijlvolle kleine stadsauto, makkelijk parkeren.', NULL, 0),
    ('Mercedes-Benz', 'C-Klasse', 2022, 75.00, 'Luxueuze en comfortabele sedan met moderne technologie.', NULL, 1);
