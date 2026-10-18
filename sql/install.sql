CREATE TABLE `mc_account` (
    `id_account` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) DEFAULT NULL,
    `firstname` VARCHAR(100) DEFAULT NULL,
    `lastname` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `is_active` TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `mc_account_social` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `id_account` INT UNSIGNED NOT NULL,
    `provider` VARCHAR(50) NOT NULL, -- Ex: 'google', 'linkedin'
    `provider_uid` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_account`) REFERENCES `mc_account`(`id_account`) ON DELETE CASCADE,
    UNIQUE KEY `idx_provider_uid` (`provider`, `provider_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NOUVELLE TABLE POUR LA CONFIGURATION DU PLUGIN
CREATE TABLE IF NOT EXISTS `mc_magixaccount_config` (
    `id_config` SMALLINT(3) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `google_enabled` TINYINT(1) NOT NULL DEFAULT 0,
    `google_client_id` VARCHAR(255) DEFAULT NULL,
    `google_client_secret` VARCHAR(255) DEFAULT NULL,
    `linkedin_enabled` TINYINT(1) NOT NULL DEFAULT 0,
    `linkedin_client_id` VARCHAR(255) DEFAULT NULL,
    `linkedin_client_secret` VARCHAR(255) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insérer une ligne de configuration par défaut
INSERT INTO `mc_magixaccount_config` (`google_enabled`, `linkedin_enabled`) VALUES (0, 0);