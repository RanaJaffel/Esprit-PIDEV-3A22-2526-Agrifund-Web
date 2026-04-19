<?php

namespace App\DataFixtures;

use App\Entity\OffreFinanciere;
use App\Entity\ProduitFinancier;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProduitOffreFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // ========== PRODUITS FINANCIERS ==========
        $produitsData = [
            [
                'nom' => 'Crédit Équipement Agricole',
                'type' => 'Crédit',
                'taux' => 7.5,
                'min' => 5000,
                'max' => 150000,
                'regles' => 'Financement pour l\'achat de tracteurs, moissonneuses et équipements agricoles modernes. Garantie sur le matériel acquis.',
            ],
            [
                'nom' => 'Prêt Saisonnier Céréales',
                'type' => 'Prêt',
                'taux' => 5.25,
                'min' => 2000,
                'max' => 50000,
                'regles' => 'Prêt à court terme pour financer les campagnes de semis et de récolte. Remboursement après la vente de la récolte.',
            ],
            [
                'nom' => 'Leasing Tracteur Premium',
                'type' => 'Leasing',
                'taux' => 8.0,
                'min' => 20000,
                'max' => 300000,
                'regles' => 'Location avec option d\'achat pour les tracteurs neufs. Durée minimale de 36 mois. Assurance incluse.',
            ],
            [
                'nom' => 'Subvention Irrigation Goutte-à-Goutte',
                'type' => 'Subvention',
                'taux' => 0,
                'min' => 1000,
                'max' => 25000,
                'regles' => 'Aide non remboursable pour l\'installation de systèmes d\'irrigation économes en eau. Cofinancement de 60%.',
            ],
            [
                'nom' => 'Microfinance Petit Exploitant',
                'type' => 'Microfinance',
                'taux' => 3.5,
                'min' => 500,
                'max' => 10000,
                'regles' => 'Microcrédits destinés aux petits agriculteurs. Pas de garantie requise. Accompagnement technique inclus.',
            ],
            [
                'nom' => 'Crédit Foncier Agricole',
                'type' => 'Crédit',
                'taux' => 6.75,
                'min' => 50000,
                'max' => 500000,
                'regles' => 'Financement pour l\'acquisition de terres agricoles. Hypothèque sur le terrain. Durée maximale de 20 ans.',
            ],
            [
                'nom' => 'Prêt Bio et Agriculture Durable',
                'type' => 'Prêt',
                'taux' => 4.0,
                'min' => 3000,
                'max' => 80000,
                'regles' => 'Taux préférentiel pour les projets d\'agriculture biologique et durable. Certification bio requise.',
            ],
            [
                'nom' => 'Leasing Serres Agricoles',
                'type' => 'Leasing',
                'taux' => 9.5,
                'min' => 15000,
                'max' => 200000,
                'regles' => 'Financement de serres multi-chapelles et tunnels. Installation et maintenance incluses la première année.',
            ],
            [
                'nom' => 'Subvention Énergie Solaire',
                'type' => 'Subvention',
                'taux' => 0,
                'min' => 5000,
                'max' => 75000,
                'regles' => 'Programme de soutien pour l\'installation de panneaux solaires sur les exploitations. Subvention de 40%.',
            ],
            [
                'nom' => 'Microfinance Élevage Ovin',
                'type' => 'Microfinance',
                'taux' => 4.5,
                'min' => 1000,
                'max' => 15000,
                'regles' => 'Financement pour l\'achat de cheptel ovin. Remboursement flexible adapté aux cycles d\'élevage.',
            ],
        ];

        $prixFixes = [
            '250.00',
            '150.00',
            '450.00',
            '120.00',
            '60.00',
            '500.00',
            '180.00',
            '320.00',
            '210.00',
            '75.00',
        ];

        $produits = [];
        foreach ($produitsData as $index => $data) {
            $produit = new ProduitFinancier();
            $produit->setNomProduit($data['nom']);
            $produit->setTypeFinancement($data['type']);
            $produit->setTauxInteret($data['taux']);
            $produit->setMontant((float) $data['max']);
            $produit->setReglesFinancieres($data['regles']);
            $produit->setPrixFixe($prixFixes[$index] ?? '0.00');

            $manager->persist($produit);
            $produits[] = $produit;
        }

        // ========== OFFRES FINANCIÈRES ==========
        $offresData = [
            ['nom' => 'Offre Printemps 2026 — Équipement', 'conditions' => 'Valable du 01/03 au 30/06/2026. Taux réduit de 0.5% pour les nouveaux clients.', 'statut' => 'Active', 'produit' => 0],
            ['nom' => 'Pack Récolte Été 2026', 'conditions' => 'Offre saisonnière pour la campagne céréalière. Différé de remboursement de 6 mois.', 'statut' => 'Active', 'produit' => 1],
            ['nom' => 'Promo Tracteur John Deere', 'conditions' => 'En partenariat avec John Deere Tunisie. Apport initial de 10% seulement.', 'statut' => 'Active', 'produit' => 2],
            ['nom' => 'Programme Irrigation 2026', 'conditions' => 'Dossier technique requis. Visite de terrain obligatoire avant validation.', 'statut' => 'En attente', 'produit' => 3],
            ['nom' => 'Micro-Crédit Express', 'conditions' => 'Déblocage en 48h. Documents simplifiés. Montant maximum de 5000 DT.', 'statut' => 'Active', 'produit' => 4],
            ['nom' => 'Offre Foncier Premium', 'conditions' => 'Réservée aux exploitants ayant plus de 5 ans d\'activité. Expertise foncière gratuite.', 'statut' => 'Active', 'produit' => 5],
            ['nom' => 'Bio Green Deal', 'conditions' => 'Certification bio en cours acceptée. Bonus de 1% sur le taux pour les coopératives.', 'statut' => 'Active', 'produit' => 6],
            ['nom' => 'Promo Serre Hiver 2025', 'conditions' => 'Offre expirée. Était valable du 01/10 au 31/12/2025.', 'statut' => 'Cancelled', 'produit' => 7],
            ['nom' => 'Solaire Été 2026', 'conditions' => 'Partenariat avec TuniSolar. Étude d\'impact environnemental incluse.', 'statut' => 'En attente', 'produit' => 8],
            ['nom' => 'Élevage Solidaire', 'conditions' => 'Programme en partenariat avec l\'ONA. Formation gratuite en gestion d\'élevage.', 'statut' => 'Active', 'produit' => 9],
            ['nom' => 'Crédit Équipement — Rentrée 2026', 'conditions' => 'Offre spéciale rentrée agricole. Livraison gratuite du matériel.', 'statut' => 'En attente', 'produit' => 0],
            ['nom' => 'Pack Semences Certifiées', 'conditions' => 'Financement des semences certifiées. Partenariat avec l\'INRAT.', 'statut' => 'Active', 'produit' => 1],
            ['nom' => 'Offre Annulée — Tests', 'conditions' => 'Offre de test annulée pour raisons administratives.', 'statut' => 'Cancelled', 'produit' => 4],
            ['nom' => 'Expansion Bio 2026', 'conditions' => 'Extension de surfaces bio. Accompagnement agronomique sur 2 ans.', 'statut' => 'Active', 'produit' => 6],
            ['nom' => 'Micro-Investissement Apicole', 'conditions' => 'Financement de ruches et matériel apicole. Formation incluse.', 'statut' => 'Active', 'produit' => 9],
        ];

        $offresPrix = [
            '99.00',
            '120.00',
            '165.00',
            '110.00',
            '55.00',
            '220.00',
            '130.00',
            '90.00',
            '115.00',
            '75.00',
            '140.00',
            '105.00',
            '65.00',
            '160.00',
            '70.00',
        ];

        foreach ($offresData as $index => $data) {
            $offre = new OffreFinanciere();
            $offre->setNomOffre($data['nom']);
            $offre->setConditions($data['conditions']);
            $offre->setStatut($data['statut']);
            $offre->setProduitFinancier($produits[$data['produit']]);
            $offre->setPrix($offresPrix[$index] ?? '0.00');

            $manager->persist($offre);
        }

        $manager->flush();
    }
}
