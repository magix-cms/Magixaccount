CREATE TABLE `mc_account` (
    `id_account` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `firstname` VARCHAR(100) DEFAULT NULL,
    `lastname` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `is_active` TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table optionnelle si on sépare strictement les adresses/données de profil
CREATE TABLE `mc_account_profile` (
    `id_profile` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `id_account` INT UNSIGNED NOT NULL,
    `phone` VARCHAR(30) DEFAULT NULL,
    `company` VARCHAR(150) DEFAULT NULL,
    FOREIGN KEY (`id_account`) REFERENCES `mc_account`(`id_account`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mc_account_reset` (
    `id_reset` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `id_account` INT UNSIGNED NOT NULL,
    `token` VARCHAR(64) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    FOREIGN KEY (`id_account`) REFERENCES `mc_account`(`id_account`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;