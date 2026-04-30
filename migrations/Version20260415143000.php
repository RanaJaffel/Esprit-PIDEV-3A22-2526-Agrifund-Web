<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260415143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add fixed payable price to produit_financier.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE produit_financier ADD prix_fixe NUMERIC(10, 2) DEFAULT 0.00 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE produit_financier DROP prix_fixe');
    }
}
