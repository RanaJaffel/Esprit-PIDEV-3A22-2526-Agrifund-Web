<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260405172512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin RENAME INDEX utilisateur_id TO UNIQ_880E0D76FB88E14F');
        $this->addSql('ALTER TABLE agriculteur CHANGE adresseferme adresseferme VARCHAR(255) DEFAULT NULL, CHANGE superficieferme superficieferme NUMERIC(10, 2) DEFAULT NULL, CHANGE type_culture type_culture VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE agriculteur RENAME INDEX utilisateur_id TO UNIQ_2366443BFB88E14F');
        $this->addSql('ALTER TABLE banque DROP FOREIGN KEY `FK_BANQUE_UTILISATEUR`');
        $this->addSql('ALTER TABLE banque CHANGE logo logo VARCHAR(255) DEFAULT NULL, CHANGE siteweb siteweb VARCHAR(255) DEFAULT NULL, CHANGE addresse_siege addresse_siege VARCHAR(255) DEFAULT NULL, CHANGE representant_legal representant_legal VARCHAR(255) DEFAULT NULL, CHANGE adresse_agence adresse_agence VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE banque RENAME INDEX codebanque TO UNIQ_B1F6CB3CD79B3EEC');
        $this->addSql('ALTER TABLE banque RENAME INDEX utilisateur_id TO UNIQ_B1F6CB3CFB88E14F');
        $this->addSql('ALTER TABLE document CHANGE date_expiration date_expiration DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE document RENAME INDEX idx_utilisateur TO IDX_D8698A76FB88E14F');
        $this->addSql('ALTER TABLE message CHANGE date_modification date_modification DATETIME DEFAULT NULL, CHANGE date_lecture date_lecture DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE message RENAME INDEX idx_conversation TO IDX_B6BD307F9AC0396');
        $this->addSql('ALTER TABLE message RENAME INDEX idx_expediteur TO IDX_B6BD307F10335F61');
        $this->addSql('ALTER TABLE piecejointe CHANGE extension extension VARCHAR(10) DEFAULT NULL, CHANGE mime_type mime_type VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE piecejointe RENAME INDEX idx_message TO IDX_E9A87772537A1329');
        $this->addSql('ALTER TABLE tokenreinitialisation CHANGE date_utilisation date_utilisation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE tokenreinitialisation RENAME INDEX token TO UNIQ_E1915BD55F37A13B');
        $this->addSql('ALTER TABLE tokenreinitialisation RENAME INDEX utilisateur_id TO IDX_E1915BD5FB88E14F');
        $this->addSql('ALTER TABLE utilisateur CHANGE tel tel VARCHAR(20) DEFAULT NULL, CHANGE photo photo VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur RENAME INDEX email TO UNIQ_1D1C63B3E7927C74');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin RENAME INDEX uniq_880e0d76fb88e14f TO utilisateur_id');
        $this->addSql('ALTER TABLE agriculteur CHANGE adresseferme adresseferme VARCHAR(255) DEFAULT \'NULL\', CHANGE superficieferme superficieferme NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE type_culture type_culture VARCHAR(100) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE agriculteur RENAME INDEX uniq_2366443bfb88e14f TO utilisateur_id');
        $this->addSql('ALTER TABLE banque CHANGE addresse_siege addresse_siege VARCHAR(255) DEFAULT \'NULL\', CHANGE representant_legal representant_legal VARCHAR(255) DEFAULT \'NULL\', CHANGE adresse_agence adresse_agence VARCHAR(255) DEFAULT \'NULL\', CHANGE logo logo VARCHAR(255) DEFAULT \'NULL\', CHANGE siteweb siteweb VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE banque RENAME INDEX uniq_b1f6cb3cfb88e14f TO utilisateur_id');
        $this->addSql('ALTER TABLE banque RENAME INDEX uniq_b1f6cb3cd79b3eec TO codebanque');
        $this->addSql('ALTER TABLE document CHANGE date_expiration date_expiration DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE document RENAME INDEX idx_d8698a76fb88e14f TO idx_utilisateur');
        $this->addSql('ALTER TABLE message CHANGE date_modification date_modification DATETIME DEFAULT \'NULL\', CHANGE date_lecture date_lecture DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE message RENAME INDEX idx_b6bd307f9ac0396 TO idx_conversation');
        $this->addSql('ALTER TABLE message RENAME INDEX idx_b6bd307f10335f61 TO idx_expediteur');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE piecejointe CHANGE extension extension VARCHAR(10) DEFAULT \'NULL\', CHANGE mime_type mime_type VARCHAR(100) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE piecejointe RENAME INDEX idx_e9a87772537a1329 TO idx_message');
        $this->addSql('ALTER TABLE tokenreinitialisation CHANGE date_utilisation date_utilisation DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE tokenreinitialisation RENAME INDEX uniq_e1915bd55f37a13b TO token');
        $this->addSql('ALTER TABLE tokenreinitialisation RENAME INDEX idx_e1915bd5fb88e14f TO utilisateur_id');
        $this->addSql('ALTER TABLE utilisateur CHANGE tel tel VARCHAR(20) DEFAULT \'NULL\', CHANGE photo photo VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE utilisateur RENAME INDEX uniq_1d1c63b3e7927c74 TO email');
    }
}
