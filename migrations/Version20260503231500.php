<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260503231500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add an index for the agriculteur resource list project/date ordering.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_ressource_project_date ON ressourceproject (idproject, dateajout, idressource)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_ressource_project_date ON ressourceproject');
    }
}
