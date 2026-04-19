-- =====================================================
-- AgriFund Database - agrifund1.sql
-- Base de données pour la plateforme de financement agricole
-- Date: 25/02/2026
-- =====================================================

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS agrifund
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE agrifund;

-- =====================================================
-- TABLE: produit_financier
-- Produits de financement disponibles
-- =====================================================
DROP TABLE IF EXISTS offre_financiere;
DROP TABLE IF EXISTS produit_financier;

CREATE TABLE produit_financier (
    id_produit       INT AUTO_INCREMENT PRIMARY KEY,
    nom_produit      VARCHAR(255) NOT NULL,
    type_financement VARCHAR(100) NOT NULL,
    taux_interet     DOUBLE       NOT NULL,
    montant          DOUBLE       NOT NULL,
    regles_financieres TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: offre_financiere
-- Offres spéciales liées aux produits financiers
-- =====================================================
CREATE TABLE offre_financiere (
    id_offre    INT AUTO_INCREMENT PRIMARY KEY,
    nom_offre   VARCHAR(255) NOT NULL,
    conditions  TEXT,
    statut      VARCHAR(50)  NOT NULL,
    prix        DOUBLE       NOT NULL DEFAULT 0,
    id_produit  INT          NOT NULL,
    CONSTRAINT fk_offre_produit
        FOREIGN KEY (id_produit) REFERENCES produit_financier(id_produit)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DONNÉES DE DÉMONSTRATION : produit_financier
-- Montants réalistes pour la Tunisie (en Dinars Tunisiens)
-- =====================================================
INSERT INTO produit_financier (nom_produit, type_financement, taux_interet, montant, regles_financieres) VALUES
('Crédit Équipement Agricole',   'Crédit',       7.50, 150000,  'Financement de matériel agricole. Durée max 7 ans. Garantie sur équipement. Accord BNA.'),
('Prêt Saisonnier Récolte',     'Prêt',         6.25, 50000,   'Financement de campagne agricole. Remboursement après récolte. Taux préférentiel BTS.'),
('Leasing Tracteur & Machines', 'Leasing',       8.50, 250000,  'Location avec option d\'achat. Maintenance incluse. Durée 3 à 7 ans. Partenariat APIA.'),
('Subvention Jeunes Agriculteurs','Subvention',   0.00, 80000,   'Aide non remboursable pour agriculteurs de moins de 40 ans. Dossier CRDA requis.'),
('Microfinance Petites Exploitations','Microfinance',9.00, 15000,   'Micro-crédit pour petites exploitations familiales. Sans garantie. Enda/TAYSIR.'),
('Crédit Irrigation Moderne',    'Crédit',       6.75, 120000,  'Installation de systèmes d\'irrigation goutte-à-goutte. Subventionné à 50% par l\'État.'),
('Prêt Élevage & Bétail',       'Prêt',         7.25, 80000,   'Acquisition de cheptel et aménagement d\'étables. Période de grâce de 6 mois.'),
('Leasing Serres Agricoles',     'Leasing',      8.00, 300000,  'Construction de serres. Contrat de 5 à 12 ans. Programme PAMPAT.'),
('Crédit Stockage & Froid',      'Crédit',       6.50, 200000,  'Chambres froides et unités de stockage. Financement jusqu\'à 70% du projet.'),
('Microfinance Apiculture',      'Microfinance', 8.25, 25000,   'Développement de ruchers. Formation GDA incluse. Remboursement flexible.');

-- =====================================================
-- DONNÉES DE DÉMONSTRATION : offre_financiere
-- Conditions adaptées au marché tunisien
-- =====================================================
INSERT INTO offre_financiere (nom_offre, conditions, statut, prix, id_produit) VALUES
('Offre Printemps 2026',             'Taux réduit de 1% sur les crédits équipement jusqu\'au 30 avril 2026.',           'Active',   5000.00, 1),
('Promo Campagne Agricole',          'Aucun frais de dossier pour les prêts saisonniers. Offre limitée.',                'Active',   2000.00, 2),
('Leasing Zéro Apport',             'Pas d\'apport initial sur le leasing tracteur pendant le mois de mars.',           'Active',  25000.00, 3),
('Bonus Jeunes Agriculteurs',        'Prime supplémentaire de 3 000 DT pour les moins de 35 ans.',                       'Active',  10000.00, 4),
('Micro-Crédit Express',            'Déblocage sous 48h pour les montants inférieurs à 5 000 DT.',                      'Active',    500.00, 5),
('Offre Irrigation Verte',          'Subvention additionnelle de 20% pour systèmes éco-responsables.',                  'Active',   8000.00, 6),
('Pack Élevage Complet',            'Crédit bétail + assurance troupeau incluse. Offre valable en 2026.',               'En attente', 3000.00, 7),
('Serres Nouvelle Génération',       'Accompagnement technique gratuit pour les 20 premiers contrats.',                  'Active',  40000.00, 8),
('Stockage Solidaire',              'Taux préférentiel pour GDA de plus de 10 membres.',                                'Active',  15000.00, 9),
('Abeilles & Miel — Offre Spéciale','Kit de démarrage offert pour tout micro-crédit apicole supérieur à 3 000 DT.',    'Active',   1000.00, 10);

-- =====================================================
-- INDEX pour les performances
-- =====================================================
CREATE INDEX idx_produit_type ON produit_financier(type_financement);
CREATE INDEX idx_offre_statut ON offre_financiere(statut);
CREATE INDEX idx_offre_produit ON offre_financiere(id_produit);
