<?php

namespace App\Command;

use App\Entity\OffreFinanciere;
use App\Entity\ProduitFinancier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-data',
    description: 'Seed the database with sample financial products and offers',
)]
class SeedDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Seeding Database with Sample Financial Data');

        $products = [
            [
                'nomProduit' => 'Greenhouse Equipment Lease',
                'typeFinancement' => 'Leasing',
                'tauxInteret' => 6.5,
                'montant' => 45000.0,
                'prixFixe' => '450.00',
                'reglesFinancieres' => 'For equipment modernization projects with a documented investment plan.',
            ],
            [
                'nomProduit' => 'Solar Pump Subsidy',
                'typeFinancement' => 'Subvention',
                'tauxInteret' => 1.5,
                'montant' => 25000.0,
                'prixFixe' => '250.00',
                'reglesFinancieres' => 'Reserved for farms adopting water-saving renewable-energy irrigation.',
            ],
            [
                'nomProduit' => 'Small Farm Microcredit',
                'typeFinancement' => 'Microfinance',
                'tauxInteret' => 4.25,
                'montant' => 12000.0,
                'prixFixe' => '120.00',
                'reglesFinancieres' => 'Designed for small farms needing short-term working capital.',
            ],
        ];

        $offers = [
            [
                'productIndex' => 0,
                'nomOffre' => 'Spring Equipment Boost',
                'conditions' => 'Priority approval for greenhouse, pump, and irrigation equipment purchases.',
                'statut' => 'Active',
                'prix' => '399.00',
            ],
            [
                'productIndex' => 1,
                'nomOffre' => 'Water Saver 2026',
                'conditions' => 'Requires proof of drip irrigation or solar pump installation.',
                'statut' => 'Active',
                'prix' => '199.00',
            ],
            [
                'productIndex' => 2,
                'nomOffre' => 'Young Farmer Starter Pack',
                'conditions' => 'Available for first-time applicants with a validated farm profile.',
                'statut' => 'En attente',
                'prix' => '99.00',
            ],
        ];

        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('DELETE FROM offre_financiere');
        $connection->executeStatement('DELETE FROM produit_financier');

        $createdProducts = [];
        foreach ($products as $productData) {
            $product = new ProduitFinancier();
            $product->setNomProduit($productData['nomProduit']);
            $product->setTypeFinancement($productData['typeFinancement']);
            $product->setTauxInteret($productData['tauxInteret']);
            $product->setMontant($productData['montant']);
            $product->setPrixFixe($productData['prixFixe']);
            $product->setReglesFinancieres($productData['reglesFinancieres']);

            $this->entityManager->persist($product);
            $createdProducts[] = $product;

            $io->writeln(sprintf('Added product: %s', $productData['nomProduit']));
        }

        foreach ($offers as $offerData) {
            $offer = new OffreFinanciere();
            $offer->setNomOffre($offerData['nomOffre']);
            $offer->setConditions($offerData['conditions']);
            $offer->setStatut($offerData['statut']);
            $offer->setPrix($offerData['prix']);
            $offer->setProduitFinancier($createdProducts[$offerData['productIndex']]);

            $this->entityManager->persist($offer);

            $io->writeln(sprintf('Added offer: %s', $offerData['nomOffre']));
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            'Database seeded successfully: %d products, %d offers.',
            count($products),
            count($offers),
        ));

        return Command::SUCCESS;
    }
}
