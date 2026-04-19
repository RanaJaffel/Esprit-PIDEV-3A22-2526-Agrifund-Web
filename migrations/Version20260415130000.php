<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260415130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create achat and transaction_paiement tables for payment core schema.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE achat (id_achat INT AUTO_INCREMENT NOT NULL, id_utilisateur INT NOT NULL, id_offre INT DEFAULT NULL, id_produit INT DEFAULT NULL, reference VARCHAR(64) NOT NULL, type_cible VARCHAR(20) NOT NULL, montant NUMERIC(10, 2) NOT NULL, devise VARCHAR(3) NOT NULL, statut VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', paid_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_achat_reference (reference), INDEX idx_achat_statut (statut), INDEX idx_achat_user (id_utilisateur), INDEX IDX_DA8F38A5A76ED395 (id_utilisateur), INDEX IDX_DA8F38A5D5F3E9C4 (id_offre), INDEX IDX_DA8F38A5F5A1AA3C (id_produit), UNIQUE INDEX UNIQ_DA8F38A5AEA34913 (reference), PRIMARY KEY(id_achat)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction_paiement (id_transaction INT AUTO_INCREMENT NOT NULL, id_achat INT NOT NULL, reference VARCHAR(64) NOT NULL, provider VARCHAR(30) NOT NULL, payment_method VARCHAR(20) NOT NULL, provider_payment_id VARCHAR(190) DEFAULT NULL, provider_event_id VARCHAR(190) DEFAULT NULL, montant NUMERIC(10, 2) NOT NULL, devise VARCHAR(3) NOT NULL, statut VARCHAR(30) NOT NULL, gateway_payload JSON DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', processed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_transaction_reference (reference), INDEX idx_transaction_provider (provider), INDEX idx_transaction_status (statut), INDEX IDX_C4ECA2BBDF51B7E8 (id_achat), UNIQUE INDEX UNIQ_C4ECA2BBAEA34913 (reference), UNIQUE INDEX UNIQ_C4ECA2BB57ECBC5F (provider_event_id), PRIMARY KEY(id_transaction)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE achat ADD CONSTRAINT FK_DA8F38A5A76ED395 FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE achat ADD CONSTRAINT FK_DA8F38A5D5F3E9C4 FOREIGN KEY (id_offre) REFERENCES offre_financiere (id_offre) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE achat ADD CONSTRAINT FK_DA8F38A5F5A1AA3C FOREIGN KEY (id_produit) REFERENCES produit_financier (id_produit) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE transaction_paiement ADD CONSTRAINT FK_C4ECA2BBDF51B7E8 FOREIGN KEY (id_achat) REFERENCES achat (id_achat) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transaction_paiement DROP FOREIGN KEY FK_C4ECA2BBDF51B7E8');
        $this->addSql('ALTER TABLE achat DROP FOREIGN KEY FK_DA8F38A5A76ED395');
        $this->addSql('ALTER TABLE achat DROP FOREIGN KEY FK_DA8F38A5D5F3E9C4');
        $this->addSql('ALTER TABLE achat DROP FOREIGN KEY FK_DA8F38A5F5A1AA3C');
        $this->addSql('DROP TABLE transaction_paiement');
        $this->addSql('DROP TABLE achat');
    }
}
