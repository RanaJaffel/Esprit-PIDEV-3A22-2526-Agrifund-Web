<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260417120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add tax breakdown and invoice email fields to achat.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE achat ADD montant_base NUMERIC(10, 2) DEFAULT 0.00 NOT NULL');
        $this->addSql('ALTER TABLE achat ADD montant_taxe NUMERIC(10, 2) DEFAULT 0.00 NOT NULL');
        $this->addSql('ALTER TABLE achat ADD invoice_email VARCHAR(180) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE achat DROP montant_base');
        $this->addSql('ALTER TABLE achat DROP montant_taxe');
        $this->addSql('ALTER TABLE achat DROP invoice_email');
    }
}
