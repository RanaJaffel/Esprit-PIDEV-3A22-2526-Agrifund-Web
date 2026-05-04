<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260504104500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a composite index for 2FA code verification lookups.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_code2fa_lookup ON code2fa (utilisateur_id, code, est_utilise, date_expiration, date_creation)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_code2fa_lookup ON code2fa');
    }
}
