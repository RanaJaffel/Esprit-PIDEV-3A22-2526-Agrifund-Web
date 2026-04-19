<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260419143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace product min/max amounts with single montant and add payment verification columns.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE produit_financier ADD montant DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('UPDATE produit_financier SET montant = COALESCE(montant_max, montant_min, 0)');
        $this->addSql('ALTER TABLE produit_financier MODIFY montant DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE produit_financier DROP montant_min, DROP montant_max');

        $this->addSql("ALTER TABLE transaction_paiement ADD verification_status VARCHAR(30) DEFAULT 'pending' NOT NULL, ADD verification_note LONGTEXT DEFAULT NULL, ADD verified_by VARCHAR(180) DEFAULT NULL, ADD verified_at DATETIME DEFAULT NULL");
        $this->addSql("UPDATE transaction_paiement SET verification_status = CASE WHEN statut = 'succeeded' THEN 'approved' WHEN statut = 'failed' THEN 'rejected' ELSE 'pending' END");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE produit_financier ADD montant_min DOUBLE PRECISION DEFAULT NULL, ADD montant_max DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('UPDATE produit_financier SET montant_min = montant, montant_max = montant');
        $this->addSql('ALTER TABLE produit_financier MODIFY montant_min DOUBLE PRECISION NOT NULL, MODIFY montant_max DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE produit_financier DROP montant');

        $this->addSql('ALTER TABLE transaction_paiement DROP verification_status, DROP verification_note, DROP verified_by, DROP verified_at');
    }
}
