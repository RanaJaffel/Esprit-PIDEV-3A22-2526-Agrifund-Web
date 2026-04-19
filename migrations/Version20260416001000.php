<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260416001000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add missing prix column to offre_financiere and backfill existing offers.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE offre_financiere ADD prix DOUBLE PRECISION DEFAULT 0 NOT NULL');
        $this->addSql('UPDATE offre_financiere o INNER JOIN produit_financier p ON p.id_produit = o.id_produit SET o.prix = CASE WHEN p.prix_fixe > 0 THEN p.prix_fixe WHEN p.montant_min > 0 THEN p.montant_min ELSE 1 END WHERE o.prix <= 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE offre_financiere DROP prix');
    }
}
