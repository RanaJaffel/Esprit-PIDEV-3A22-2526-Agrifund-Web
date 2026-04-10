<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260410203021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, UNIQUE INDEX UNIQ_880E0D76FB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE agriculteur (id INT AUTO_INCREMENT NOT NULL, adresseferme VARCHAR(255) DEFAULT NULL, superficieferme NUMERIC(10, 2) DEFAULT NULL, type_culture VARCHAR(100) DEFAULT NULL, statuscompte VARCHAR(50) NOT NULL, compteverifie TINYINT NOT NULL, utilisateur_id INT NOT NULL, UNIQUE INDEX UNIQ_2366443BFB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE admin ADD CONSTRAINT FK_880E0D76FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE agriculteur ADD CONSTRAINT FK_2366443BFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE capteur DROP FOREIGN KEY `fk_capteur_projet`');
        $this->addSql('ALTER TABLE offre_financiere DROP FOREIGN KEY `fk_offre_banque`');
        $this->addSql('ALTER TABLE offre_financiere DROP FOREIGN KEY `fk_produit`');
        $this->addSql('ALTER TABLE produit_financier DROP FOREIGN KEY `fk_produit_banque`');
        $this->addSql('ALTER TABLE rapport_journalier DROP FOREIGN KEY `fk_rapport_projet`');
        $this->addSql('ALTER TABLE releve_terrain DROP FOREIGN KEY `releve_terrain_ibfk_1`');
        $this->addSql('DROP TABLE capteur');
        $this->addSql('DROP TABLE code2fa');
        $this->addSql('DROP TABLE decisionfinanciere');
        $this->addSql('DROP TABLE donnees_satellite');
        $this->addSql('DROP TABLE evaluationrisque');
        $this->addSql('DROP TABLE historiqueconnexion');
        $this->addSql('DROP TABLE offre_financiere');
        $this->addSql('DROP TABLE parametres2fa');
        $this->addSql('DROP TABLE produit_financier');
        $this->addSql('DROP TABLE projectagricole');
        $this->addSql('DROP TABLE rapport_journalier');
        $this->addSql('DROP TABLE releve_terrain');
        $this->addSql('DROP TABLE ressourceproject');
        $this->addSql('DROP TABLE statutenligne');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP INDEX idx_code ON banque');
        $this->addSql('DROP INDEX idx_status ON banque');
        $this->addSql('ALTER TABLE banque ADD addresse_siege VARCHAR(255) DEFAULT NULL, ADD representant_legal VARCHAR(255) DEFAULT NULL, ADD adresse_agence VARCHAR(255) DEFAULT NULL, ADD status_compte VARCHAR(50) NOT NULL, ADD compte_verfiee TINYINT NOT NULL, DROP addresseSiege, DROP representantLegal, DROP adresseAgence, DROP statusCompte, DROP compteVerfiee, CHANGE logo logo VARCHAR(255) DEFAULT NULL, CHANGE siteweb siteweb VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE banque ADD CONSTRAINT FK_B1F6CB3CFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE banque RENAME INDEX codebanque TO UNIQ_B1F6CB3CD79B3EEC');
        $this->addSql('ALTER TABLE banque RENAME INDEX utilisateur_id TO UNIQ_B1F6CB3CFB88E14F');
        $this->addSql('DROP INDEX idx_activite ON conversation');
        $this->addSql('DROP INDEX idx_utilisateur1 ON conversation');
        $this->addSql('DROP INDEX idx_utilisateur2 ON conversation');
        $this->addSql('ALTER TABLE conversation CHANGE utilisateur_min utilisateur_min INT NOT NULL, CHANGE utilisateur_max utilisateur_max INT NOT NULL, CHANGE date_creation date_creation DATETIME NOT NULL, CHANGE derniere_activite derniere_activite DATETIME NOT NULL');
        $this->addSql('DROP INDEX idx_statut ON document');
        $this->addSql('DROP INDEX idx_type ON document');
        $this->addSql('ALTER TABLE document CHANGE date_upload date_upload DATETIME NOT NULL, CHANGE date_expiration date_expiration DATE DEFAULT NULL, CHANGE statut statut VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document RENAME INDEX idx_utilisateur TO IDX_D8698A76FB88E14F');
        $this->addSql('DROP INDEX idx_message_lecture ON message');
        $this->addSql('DROP INDEX idx_date ON message');
        $this->addSql('DROP INDEX idx_lu ON message');
        $this->addSql('ALTER TABLE message CHANGE contenu contenu LONGTEXT NOT NULL, CHANGE a_piece_jointe a_piece_jointe TINYINT NOT NULL, CHANGE nb_pieces_jointes nb_pieces_jointes INT NOT NULL, CHANGE date_envoi date_envoi DATETIME NOT NULL, CHANGE date_modification date_modification DATETIME DEFAULT NULL, CHANGE est_lu est_lu TINYINT NOT NULL, CHANGE est_supprime est_supprime TINYINT NOT NULL, CHANGE date_lecture date_lecture DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F10335F61 FOREIGN KEY (expediteur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message RENAME INDEX idx_conversation TO IDX_B6BD307F9AC0396');
        $this->addSql('ALTER TABLE message RENAME INDEX idx_expediteur TO IDX_B6BD307F10335F61');
        $this->addSql('DROP INDEX idx_date ON piecejointe');
        $this->addSql('DROP INDEX idx_type ON piecejointe');
        $this->addSql('ALTER TABLE piecejointe CHANGE type_fichier type_fichier VARCHAR(20) NOT NULL, CHANGE extension extension VARCHAR(10) DEFAULT NULL, CHANGE mime_type mime_type VARCHAR(100) DEFAULT NULL, CHANGE date_upload date_upload DATETIME NOT NULL');
        $this->addSql('ALTER TABLE piecejointe ADD CONSTRAINT FK_E9A87772537A1329 FOREIGN KEY (message_id) REFERENCES message (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE piecejointe RENAME INDEX idx_message TO IDX_E9A87772537A1329');
        $this->addSql('DROP INDEX idx_expiration ON tokenreinitialisation');
        $this->addSql('DROP INDEX idx_token ON tokenreinitialisation');
        $this->addSql('ALTER TABLE tokenreinitialisation CHANGE utilise utilise TINYINT NOT NULL, CHANGE date_utilisation date_utilisation DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME NOT NULL');
        $this->addSql('ALTER TABLE tokenreinitialisation ADD CONSTRAINT FK_E1915BD5FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tokenreinitialisation RENAME INDEX token TO UNIQ_E1915BD55F37A13B');
        $this->addSql('ALTER TABLE tokenreinitialisation RENAME INDEX utilisateur_id TO IDX_E1915BD5FB88E14F');
        $this->addSql('DROP INDEX idx_email ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur CHANGE tel tel VARCHAR(20) DEFAULT NULL, CHANGE date_inscrit date_inscrit DATETIME NOT NULL, CHANGE photo photo VARCHAR(255) DEFAULT NULL, CHANGE derniere_connexion derniere_connexion DATETIME NOT NULL, CHANGE est_en_ligne est_en_ligne TINYINT NOT NULL');
        $this->addSql('ALTER TABLE utilisateur RENAME INDEX email TO UNIQ_1D1C63B3E7927C74');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE capteur (id_capteur INT AUTO_INCREMENT NOT NULL, typeCapteur VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, localisation VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, statut VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT \'\'\'ACTIF\'\'\' NOT NULL COLLATE `utf8mb4_general_ci`, date_installation DATETIME DEFAULT \'current_timestamp()\' NOT NULL, idproject INT DEFAULT NULL, INDEX fk_capteur_projet (idproject), INDEX idx_statut (statut), INDEX idx_type (typeCapteur), PRIMARY KEY (id_capteur)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE code2fa (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, code VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, date_creation DATETIME DEFAULT \'current_timestamp()\', date_expiration DATETIME NOT NULL, est_utilise TINYINT DEFAULT 0, date_utilisation DATETIME DEFAULT \'NULL\', type_envoi ENUM(\'email\', \'sms\') CHARACTER SET utf8mb4 DEFAULT \'\'\'email\'\'\' COLLATE `utf8mb4_general_ci`, INDEX idx_code (code), INDEX idx_expiration (date_expiration), INDEX idx_utilisateur (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE decisionfinanciere (idDecision INT AUTO_INCREMENT NOT NULL, statut VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, justification TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, dateDecision DATETIME NOT NULL, idEvaluation INT NOT NULL, banqueId INT DEFAULT NULL, INDEX fk_decision_banque (banqueId), INDEX decisionfinanciere_ibfk_1 (idEvaluation), PRIMARY KEY (idDecision)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE donnees_satellite (id INT AUTO_INCREMENT NOT NULL, agriculteur_id INT DEFAULT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, date_mesure DATE NOT NULL, ndvi DOUBLE PRECISION DEFAULT \'NULL\' COMMENT \'Indice de végétation normalisé (-1 à 1)\', temperature_moyenne DOUBLE PRECISION DEFAULT \'NULL\' COMMENT \'Température moyenne en °C\', precipitation DOUBLE PRECISION DEFAULT \'NULL\' COMMENT \'Précipitations en mm\', humidite DOUBLE PRECISION DEFAULT \'NULL\' COMMENT \'Humidité relative en %\', indice_secheresse DOUBLE PRECISION DEFAULT \'NULL\' COMMENT \'Indice de sécheresse (0-100)\', risque_agricole VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci` COMMENT \'faible, moyen, eleve, critique\', donnees_brutes LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_bin` COMMENT \'Données JSON brutes de l\'\'API\', date_creation DATETIME DEFAULT \'current_timestamp()\' NOT NULL, INDEX agriculteur_id (agriculteur_id), INDEX idx_satellite_date (date_mesure), INDEX idx_satellite_coords (latitude, longitude), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE evaluationrisque (idEvaluation INT AUTO_INCREMENT NOT NULL, scoreGlobal INT NOT NULL, niveauRisque VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, fiabiliteDonnees VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, facteurPrincipal TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, recommandation INT NOT NULL, dateEvaluation DATETIME NOT NULL, idProjet INT NOT NULL, banqueId INT DEFAULT NULL, INDEX fk_evaluation_projectagricole (idProjet), INDEX fk_evaluation_banque (banqueId), PRIMARY KEY (idEvaluation)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE historiqueconnexion (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, date_connexion DATETIME DEFAULT \'current_timestamp()\', adresse_ip VARCHAR(45) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, navigateur VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, systeme_exploitation VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, connexion_reussie TINYINT DEFAULT 1, methode_auth ENUM(\'password\', \'2fa\', \'token\') CHARACTER SET utf8mb4 DEFAULT \'\'\'password\'\'\' COLLATE `utf8mb4_general_ci`, localisation VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, INDEX idx_reussie (connexion_reussie), INDEX idx_utilisateur (utilisateur_id), INDEX idx_date (date_connexion), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE offre_financiere (id_offre INT AUTO_INCREMENT NOT NULL, nom_offre VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, conditions TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, statut VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, id_produit INT NOT NULL, banque_id INT DEFAULT NULL, INDEX fk_offre_banque (banque_id), INDEX fk_produit (id_produit), PRIMARY KEY (id_offre)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE parametres2fa (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, est_active TINYINT DEFAULT 0, methode_preferee ENUM(\'email\', \'sms\', \'desactive\') CHARACTER SET utf8mb4 DEFAULT \'\'\'email\'\'\' COLLATE `utf8mb4_general_ci`, telephone_2fa VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, date_activation DATETIME DEFAULT \'NULL\', UNIQUE INDEX utilisateur_id (utilisateur_id), INDEX idx_utilisateur (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE produit_financier (id_produit INT AUTO_INCREMENT NOT NULL, nom_produit VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, type_financement VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, taux_interet DOUBLE PRECISION NOT NULL, montant_min DOUBLE PRECISION NOT NULL, montant_max DOUBLE PRECISION NOT NULL, regles_financieres TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, banque_id INT DEFAULT NULL, INDEX fk_produit_banque (banque_id), PRIMARY KEY (id_produit)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE projectagricole (idproject INT AUTO_INCREMENT NOT NULL, agriculteur_id INT NOT NULL, nomproject VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, surface FLOAT NOT NULL, budgetdemande NUMERIC(12, 2) NOT NULL, statut ENUM(\'en cours\', \'accepte\', \'refuse\') CHARACTER SET utf8mb4 DEFAULT \'\'\'en cours\'\'\' NOT NULL COLLATE `utf8mb4_general_ci`, datesoumission DATE NOT NULL, latitude DOUBLE PRECISION DEFAULT \'NULL\', longitude DOUBLE PRECISION DEFAULT \'NULL\', INDEX fk_project_agriculteur (agriculteur_id), PRIMARY KEY (idproject)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE rapport_journalier (id_rapport INT AUTO_INCREMENT NOT NULL, date_rapport DATE NOT NULL, type_mesure VARCHAR(50) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, moyenne DOUBLE PRECISION NOT NULL, min DOUBLE PRECISION NOT NULL, max DOUBLE PRECISION NOT NULL, id_capteur INT NOT NULL, idproject INT DEFAULT NULL, valeur_mesuree DOUBLE PRECISION DEFAULT \'NULL\', INDEX fk_rapport_projet (idproject), PRIMARY KEY (id_rapport)) DEFAULT CHARACTER SET latin1 COLLATE `latin1_swedish_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE releve_terrain (id_releve INT AUTO_INCREMENT NOT NULL, type_mesure VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, valeur_mesuree DOUBLE PRECISION NOT NULL, unite VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, date_heure DATETIME DEFAULT \'current_timestamp()\' NOT NULL, id_capteur INT NOT NULL, INDEX idx_date (date_heure), INDEX idx_type (type_mesure), INDEX idx_capteur (id_capteur), PRIMARY KEY (id_releve)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ressourceproject (idressource INT AUTO_INCREMENT NOT NULL, nomressource VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, typeressource ENUM(\'equipement\', \'materiaux\', \'service\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, quantite INT NOT NULL, cout NUMERIC(12, 2) NOT NULL, fournisseur VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, statut ENUM(\'prevu\', \'achete\') CHARACTER SET utf8mb4 DEFAULT \'\'\'prevu\'\'\' COLLATE `utf8mb4_general_ci`, dateajout DATE NOT NULL, idproject INT NOT NULL, INDEX idproject (idproject), PRIMARY KEY (idressource)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE statutenligne (utilisateur_id INT NOT NULL, est_en_ligne TINYINT DEFAULT 0, derniere_activite DATETIME DEFAULT \'current_timestamp()\' NOT NULL, INDEX idx_statut_utilisateur (utilisateur_id), PRIMARY KEY (utilisateur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`, password VARCHAR(255) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, firstname VARCHAR(255) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, lastname VARCHAR(255) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX email (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET latin1 COLLATE `latin1_swedish_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE capteur ADD CONSTRAINT `fk_capteur_projet` FOREIGN KEY (idproject) REFERENCES projectagricole (idproject) ON UPDATE CASCADE ON DELETE SET NULL');
        $this->addSql('ALTER TABLE offre_financiere ADD CONSTRAINT `fk_offre_banque` FOREIGN KEY (banque_id) REFERENCES banque (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE offre_financiere ADD CONSTRAINT `fk_produit` FOREIGN KEY (id_produit) REFERENCES produit_financier (id_produit) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produit_financier ADD CONSTRAINT `fk_produit_banque` FOREIGN KEY (banque_id) REFERENCES banque (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE rapport_journalier ADD CONSTRAINT `fk_rapport_projet` FOREIGN KEY (idproject) REFERENCES projectagricole (idproject) ON UPDATE CASCADE ON DELETE SET NULL');
        $this->addSql('ALTER TABLE releve_terrain ADD CONSTRAINT `releve_terrain_ibfk_1` FOREIGN KEY (id_capteur) REFERENCES capteur (id_capteur) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE admin DROP FOREIGN KEY FK_880E0D76FB88E14F');
        $this->addSql('ALTER TABLE agriculteur DROP FOREIGN KEY FK_2366443BFB88E14F');
        $this->addSql('DROP TABLE admin');
        $this->addSql('DROP TABLE agriculteur');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE banque DROP FOREIGN KEY FK_B1F6CB3CFB88E14F');
        $this->addSql('ALTER TABLE banque ADD addresseSiege VARCHAR(255) DEFAULT \'NULL\', ADD representantLegal VARCHAR(255) DEFAULT \'NULL\', ADD adresseAgence VARCHAR(255) DEFAULT \'NULL\', ADD statusCompte VARCHAR(50) DEFAULT \'\'\'en_attente\'\'\', ADD compteVerfiee TINYINT DEFAULT 0, DROP addresse_siege, DROP representant_legal, DROP adresse_agence, DROP status_compte, DROP compte_verfiee, CHANGE logo logo VARCHAR(255) DEFAULT \'NULL\', CHANGE siteweb siteweb VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('CREATE INDEX idx_code ON banque (codebanque)');
        $this->addSql('CREATE INDEX idx_status ON banque (statusCompte)');
        $this->addSql('ALTER TABLE banque RENAME INDEX uniq_b1f6cb3cfb88e14f TO utilisateur_id');
        $this->addSql('ALTER TABLE banque RENAME INDEX uniq_b1f6cb3cd79b3eec TO codebanque');
        $this->addSql('ALTER TABLE conversation CHANGE utilisateur_min utilisateur_min INT DEFAULT NULL, CHANGE utilisateur_max utilisateur_max INT DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT \'current_timestamp()\', CHANGE derniere_activite derniere_activite DATETIME DEFAULT \'current_timestamp()\'');
        $this->addSql('CREATE INDEX idx_activite ON conversation (derniere_activite)');
        $this->addSql('CREATE INDEX idx_utilisateur1 ON conversation (utilisateur1_id)');
        $this->addSql('CREATE INDEX idx_utilisateur2 ON conversation (utilisateur2_id)');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76FB88E14F');
        $this->addSql('ALTER TABLE document CHANGE date_upload date_upload DATETIME DEFAULT \'current_timestamp()\', CHANGE date_expiration date_expiration DATE DEFAULT \'NULL\', CHANGE statut statut VARCHAR(20) DEFAULT \'\'\'en_attente\'\'\'');
        $this->addSql('CREATE INDEX idx_statut ON document (statut)');
        $this->addSql('CREATE INDEX idx_type ON document (type_document)');
        $this->addSql('ALTER TABLE document RENAME INDEX idx_d8698a76fb88e14f TO idx_utilisateur');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F9AC0396');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F10335F61');
        $this->addSql('ALTER TABLE message CHANGE contenu contenu TEXT NOT NULL, CHANGE a_piece_jointe a_piece_jointe TINYINT DEFAULT 0, CHANGE nb_pieces_jointes nb_pieces_jointes INT DEFAULT 0, CHANGE date_envoi date_envoi DATETIME DEFAULT \'current_timestamp()\', CHANGE date_modification date_modification DATETIME DEFAULT \'NULL\', CHANGE est_lu est_lu TINYINT DEFAULT 0, CHANGE est_supprime est_supprime TINYINT DEFAULT 0, CHANGE date_lecture date_lecture DATETIME DEFAULT \'NULL\'');
        $this->addSql('CREATE INDEX idx_message_lecture ON message (date_lecture)');
        $this->addSql('CREATE INDEX idx_date ON message (date_envoi)');
        $this->addSql('CREATE INDEX idx_lu ON message (est_lu)');
        $this->addSql('ALTER TABLE message RENAME INDEX idx_b6bd307f10335f61 TO idx_expediteur');
        $this->addSql('ALTER TABLE message RENAME INDEX idx_b6bd307f9ac0396 TO idx_conversation');
        $this->addSql('ALTER TABLE piecejointe DROP FOREIGN KEY FK_E9A87772537A1329');
        $this->addSql('ALTER TABLE piecejointe CHANGE type_fichier type_fichier ENUM(\'image\', \'document\', \'audio\', \'video\', \'autre\') NOT NULL, CHANGE extension extension VARCHAR(10) DEFAULT \'NULL\', CHANGE mime_type mime_type VARCHAR(100) DEFAULT \'NULL\', CHANGE date_upload date_upload DATETIME DEFAULT \'current_timestamp()\'');
        $this->addSql('CREATE INDEX idx_date ON piecejointe (date_upload)');
        $this->addSql('CREATE INDEX idx_type ON piecejointe (type_fichier)');
        $this->addSql('ALTER TABLE piecejointe RENAME INDEX idx_e9a87772537a1329 TO idx_message');
        $this->addSql('ALTER TABLE tokenreinitialisation DROP FOREIGN KEY FK_E1915BD5FB88E14F');
        $this->addSql('ALTER TABLE tokenreinitialisation CHANGE utilise utilise TINYINT DEFAULT 0, CHANGE date_utilisation date_utilisation DATETIME DEFAULT \'NULL\', CHANGE date_creation date_creation DATETIME DEFAULT \'current_timestamp()\'');
        $this->addSql('CREATE INDEX idx_expiration ON tokenreinitialisation (date_expiration)');
        $this->addSql('CREATE INDEX idx_token ON tokenreinitialisation (token)');
        $this->addSql('ALTER TABLE tokenreinitialisation RENAME INDEX uniq_e1915bd55f37a13b TO token');
        $this->addSql('ALTER TABLE tokenreinitialisation RENAME INDEX idx_e1915bd5fb88e14f TO utilisateur_id');
        $this->addSql('ALTER TABLE utilisateur CHANGE tel tel VARCHAR(20) DEFAULT \'NULL\', CHANGE date_inscrit date_inscrit DATETIME DEFAULT \'current_timestamp()\', CHANGE photo photo VARCHAR(255) DEFAULT \'NULL\', CHANGE derniere_connexion derniere_connexion DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE est_en_ligne est_en_ligne TINYINT DEFAULT 0');
        $this->addSql('CREATE INDEX idx_email ON utilisateur (email)');
        $this->addSql('ALTER TABLE utilisateur RENAME INDEX uniq_1d1c63b3e7927c74 TO email');
    }
}
