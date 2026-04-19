<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260419193000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Runtime reconciliation for admin/agriculteur/banque pages: missing columns, messenger table, and verification tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE utilisateur
            ADD COLUMN IF NOT EXISTS is_verified TINYINT(1) NOT NULL DEFAULT 0,
            ADD COLUMN IF NOT EXISTS face_descriptor LONGTEXT DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS face_enabled TINYINT(1) NOT NULL DEFAULT 0,
            ADD COLUMN IF NOT EXISTS face_enrolled_at DATETIME DEFAULT NULL');

        $this->addSql('UPDATE utilisateur SET is_verified = 1 WHERE is_verified = 0');

        $this->addSql('ALTER TABLE capteur
            ADD COLUMN IF NOT EXISTS modele VARCHAR(100) NOT NULL DEFAULT \'Simulateur Python\',
            ADD COLUMN IF NOT EXISTS id_user INT DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS last_seen_at DATETIME DEFAULT NULL');

        $this->addSql('CREATE TABLE IF NOT EXISTS messenger_messages (
            id BIGINT AUTO_INCREMENT NOT NULL,
            body LONGTEXT NOT NULL,
            headers LONGTEXT NOT NULL,
            queue_name VARCHAR(190) NOT NULL,
            created_at DATETIME NOT NULL,
            available_at DATETIME NOT NULL,
            delivered_at DATETIME DEFAULT NULL,
            INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id),
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS verification_token (
            id INT AUTO_INCREMENT NOT NULL,
            utilisateur_id INT NOT NULL,
            token VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_VERIFICATION_TOKEN_TOKEN (token),
            UNIQUE INDEX UNIQ_VERIFICATION_TOKEN_USER (utilisateur_id),
            INDEX IDX_VERIFICATION_TOKEN_USER (utilisateur_id),
            PRIMARY KEY (id),
            CONSTRAINT FK_VERIFICATION_TOKEN_USER FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS face_recognition_log (
            id INT AUTO_INCREMENT NOT NULL,
            utilisateur_id INT NOT NULL,
            success TINYINT(1) NOT NULL DEFAULT 0,
            confidence DOUBLE PRECISION DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            INDEX IDX_FACE_LOG_USER (utilisateur_id),
            INDEX IDX_FACE_LOG_USER_SUCCESS (utilisateur_id, success),
            INDEX IDX_FACE_LOG_CREATED_AT (created_at),
            PRIMARY KEY(id),
            CONSTRAINT FK_FACE_LOG_USER FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // Intentionally non-destructive rollback for reconciliation migration.
    }
}
