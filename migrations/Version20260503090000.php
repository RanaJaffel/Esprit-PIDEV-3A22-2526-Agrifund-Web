<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260503090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store Symfony security roles on utilisateur to avoid loading role profile relations during authorization.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE utilisateur ADD roles LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)'");
        $this->addSql("UPDATE utilisateur SET roles = '[]' WHERE roles IS NULL");
        $this->addSql("UPDATE utilisateur u SET roles = '[\"ROLE_ADMIN\"]' WHERE EXISTS (SELECT 1 FROM admin a WHERE a.utilisateur_id = u.id)");
        $this->addSql("UPDATE utilisateur u SET roles = '[\"ROLE_AGRICULTEUR\"]' WHERE EXISTS (SELECT 1 FROM agriculteur ag WHERE ag.utilisateur_id = u.id)");
        $this->addSql("UPDATE utilisateur u SET roles = '[\"ROLE_BANQUE\"]' WHERE EXISTS (SELECT 1 FROM banque b WHERE b.utilisateur_id = u.id)");
        $this->addSql("ALTER TABLE utilisateur MODIFY roles LONGTEXT NOT NULL COMMENT '(DC2Type:json)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE utilisateur DROP roles');
    }
}
