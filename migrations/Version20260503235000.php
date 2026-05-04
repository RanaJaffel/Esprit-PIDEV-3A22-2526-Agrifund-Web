<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260503235000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Recreate parametres2fa table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE parametres2fa (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, est_active TINYINT DEFAULT 0, methode_preferee ENUM(\'email\', \'sms\', \'desactive\') DEFAULT \'email\', telephone_2fa VARCHAR(20) DEFAULT NULL, date_activation DATETIME DEFAULT NULL, UNIQUE INDEX utilisateur_id (utilisateur_id), INDEX idx_utilisateur (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE parametres2fa ADD CONSTRAINT parametres2fa_ibfk_1 FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE parametres2fa');
    }
}
