<?php

namespace App\Command;

use App\Entity\Product;
use App\Entity\Offer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsCommand(
    name: 'app:seed-data',
    description: 'Seed the database with sample products and offers',
)]
class SeedDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Seeding Database with Sample Data');

        // Sample Products
        $products = [
            [
                'name' => 'Engrais Bio Premium',
                'description' => 'Engrais organique 100% naturel riche en nutriments essentiels pour tous types de cultures. Améliore la structure du sol et favorise la croissance des plantes.',
                'price' => 45.99,
            ],
            [
                'name' => 'Semences Maïs Hybride',
                'description' => 'Variété hybride haute performance avec rendement exceptionnel. Résistant aux maladies et aux conditions climatiques difficiles. Idéal pour les grandes exploitations.',
                'price' => 120.50,
            ],
            [
                'name' => 'Pesticide Naturel F400',
                'description' => 'Solution phytosanitaire naturelle sans résidu chimique. Efficace contre les insectes nuisibles tout en préservant les pollinisateurs et l\'environnement.',
                'price' => 65.00,
            ],
            [
                'name' => 'Kit Irrigation Goutte-à-Goutte',
                'description' => 'Système d\'irrigation drip complet pour petites et moyennes surfaces. Économise jusqu\'à 60% d\'eau avec une distribution précise et uniforme.',
                'price' => 189.99,
            ],
            [
                'name' => 'Laboureur Professionnel 2000W',
                'description' => 'Motoculteur puissant pour ameublir et préparer les sols. Largeur de travail ajustable de 30 à 60cm. Parfait pour légumes et cultures maraîchères.',
                'price' => 599.00,
            ],
            [
                'name' => 'Compost Premium 50kg',
                'description' => 'Compost riche en matière organique, prêt à l\'emploi. Améliore la fertilité du sol et augmente la rétention d\'eau. Parfait pour tous les types de cultures.',
                'price' => 35.50,
            ],
        ];

        // Sample Offers
        $offers = [
            [
                'title' => 'Promo Printemps 2026',
                'description' => 'Profitez de -20% sur tous les engrais et semences jusqu\'à fin avril. Offre valable sur l\'ensemble du catalogue agricole.',
                'discount' => 20,
                'endDate' => '2026-04-30',
            ],
            [
                'title' => 'Pack Irrigation Complet',
                'description' => 'Achetez un kit d\'irrigation et recevez 1 tuyau gratuit + spray adapté. Économisez jusqu\'à 150€ sur votre installation.',
                'discount' => 30,
                'endDate' => '2026-04-15',
            ],
            [
                'title' => 'Offre Engrais & Protection',
                'description' => 'Combo spécial: 1 sac engrais + 1 pesticide naturel à prix réduit. Retrouvez tous les nutriments essentiels pour une culture saine.',
                'discount' => 15,
                'endDate' => '2026-05-31',
            ],
            [
                'title' => 'Week-end Promo Matériel',
                'description' => 'Gros réductions sur tous les équipements de jardinage et d\'agriculture. Ce week-end uniquement: -25% sur les motoculteurs!',
                'discount' => 25,
                'endDate' => '2026-04-06',
            ],
        ];

        // Clear existing data (optional)
        $io->writeln('🗑️  Clearing existing data...');
        $this->entityManager->getConnection()->executeStatement('DELETE FROM products');
        $this->entityManager->getConnection()->executeStatement('DELETE FROM offers');

        // Insert Products
        $io->writeln("\n📦 Adding products...");
        foreach ($products as $productData) {
            $product = new Product();
            $product->setName($productData['name']);
            $product->setDescription($productData['description']);
            $product->setPrice($productData['price']);
            $product->setSlug($this->slugger->slug($productData['name'])->lower()->toString());
            $product->setIsActive(true);

            $this->entityManager->persist($product);
            $io->writeln("  ✓ Added: {$productData['name']} ({$productData['price']}€)");
        }

        // Insert Offers
        $io->writeln("\n🎁 Adding offers...");
        foreach ($offers as $offerData) {
            $offer = new Offer();
            $offer->setTitle($offerData['title']);
            $offer->setDescription($offerData['description']);
            $offer->setDiscount($offerData['discount']);
            $offer->setEndDate(new \DateTime($offerData['endDate']));
            $offer->setSlug($this->slugger->slug($offerData['title'])->lower()->toString());
            $offer->setIsActive(true);

            $this->entityManager->persist($offer);
            $io->writeln("  ✓ Added: {$offerData['title']} (-{$offerData['discount']}%)");
        }

        $this->entityManager->flush();

        $io->success('✅ Database seeded successfully!');
        $io->writeln([
            '',
            '📊 Summary:',
            "   • " . count($products) . " products added",
            "   • " . count($offers) . " offers added",
            '',
            '🔗 Access your data:',
            '   • Admin Panel: http://localhost:8000/admin/produits/',
            '   • Products: http://localhost:8000/admin/produits/',
            '   • Offers: http://localhost:8000/admin/offres/',
            '   • Dashboard: http://localhost:8000/admin/produits/',
        ]);

        return Command::SUCCESS;
    }
}
