<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260419120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store offer price as decimal(10,2) with default value.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE offre_financiere SET prix = 0.00 WHERE prix IS NULL');
        $this->addSql('ALTER TABLE offre_financiere CHANGE prix prix NUMERIC(10, 2) DEFAULT 0.00 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE offre_financiere CHANGE prix prix DOUBLE PRECISION DEFAULT NULL');
    }
}
