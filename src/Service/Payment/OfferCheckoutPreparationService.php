<?php

declare(strict_types=1);

namespace App\Service\Payment;

use App\Entity\Achat;
use App\Entity\OffreFinanciere;
use App\Entity\ProduitFinancier;
use App\Entity\TransactionPaiement;
use App\Entity\Utilisateur;
use App\Payment\AchatStatus;
use App\Payment\PaymentProvider;
use App\Payment\PaymentStatus;

class OfferCheckoutPreparationService
{
    private const DEFAULT_TAX_RATE = 0.05;

    /**
     * @return array{Achat, TransactionPaiement}
     */
    public function prepare(
        Utilisateur $utilisateur,
        OffreFinanciere $offre,
        ?string $submittedAmount = null,
        string $provider = PaymentProvider::MANUAL,
        string $paymentMethod = TransactionPaiement::METHOD_CARD
    ): array
    {
        if ($offre->getStatut() !== 'Active') {
            throw new \InvalidArgumentException('Offre indisponible.');
        }

        $prixOffre = (float) ($offre->getPrix() ?? 0.0);
        if ($prixOffre <= 0) {
            throw new \InvalidArgumentException('Offre sans prix payable.');
        }

        // The submitted amount is intentionally ignored to prevent client-side tampering.
        $breakdown = $this->computeBreakdown($prixOffre);
        $amountBase = $breakdown['base'];
        $amountTax = $breakdown['tax'];
        $amountTotal = $breakdown['total'];

        $achat = new Achat();
        $achat->setReference($this->generateReference('ACH'));
        $achat->setUtilisateur($utilisateur);
        $achat->setOffre($offre);
        $achat->setTypeCible(Achat::TYPE_OFFER);
        $achat->setMontantBase($amountBase);
        $achat->setMontantTaxe($amountTax);
        $achat->setMontant($amountTotal);
        $achat->setDevise('TND');
        $achat->setStatut(AchatStatus::PENDING_GATEWAY);

        $transaction = new TransactionPaiement();
        $transaction->setReference($this->generateReference('PAY'));
        $transaction->setAchat($achat);
        $transaction->setProvider($provider);
        $transaction->setPaymentMethod($paymentMethod);
        $transaction->setMontant($amountTotal);
        $transaction->setDevise('TND');
        $transaction->setStatut(PaymentStatus::PENDING);
        $transaction->setVerificationStatus('pending');

        return [$achat, $transaction];
    }

    /**
     * @return array{Achat, TransactionPaiement}
     */
    public function prepareProduct(Utilisateur $utilisateur, ProduitFinancier $produit, ?string $submittedAmount = null): array
    {
        $prixFixe = (float) $produit->getPrixFixe();
        if ($prixFixe <= 0) {
            throw new \InvalidArgumentException('Produit sans prix payable.');
        }

        // The submitted amount is intentionally ignored to prevent client-side tampering.
        $breakdown = $this->computeBreakdown($prixFixe);
        $amountBase = $breakdown['base'];
        $amountTax = $breakdown['tax'];
        $amountTotal = $breakdown['total'];

        $achat = new Achat();
        $achat->setReference($this->generateReference('ACH'));
        $achat->setUtilisateur($utilisateur);
        $achat->setProduit($produit);
        $achat->setTypeCible(Achat::TYPE_PRODUCT);
        $achat->setMontantBase($amountBase);
        $achat->setMontantTaxe($amountTax);
        $achat->setMontant($amountTotal);
        $achat->setDevise('TND');
        $achat->setStatut(AchatStatus::PENDING_GATEWAY);

        $transaction = new TransactionPaiement();
        $transaction->setReference($this->generateReference('PAY'));
        $transaction->setAchat($achat);
        $transaction->setProvider(PaymentProvider::MANUAL);
        $transaction->setPaymentMethod(TransactionPaiement::METHOD_CARD);
        $transaction->setMontant($amountTotal);
        $transaction->setDevise('TND');
        $transaction->setStatut(PaymentStatus::PENDING);
        $transaction->setVerificationStatus('pending');

        return [$achat, $transaction];
    }

    /**
     * @return array{Achat, TransactionPaiement}
     */
    public function prepareProductForProvider(
        Utilisateur $utilisateur,
        ProduitFinancier $produit,
        ?string $submittedAmount = null,
        string $provider = PaymentProvider::MANUAL,
        string $paymentMethod = TransactionPaiement::METHOD_CARD
    ): array {
        [$achat, $transaction] = $this->prepareProduct($utilisateur, $produit, $submittedAmount);
        $transaction->setProvider($provider);
        $transaction->setPaymentMethod($paymentMethod);

        return [$achat, $transaction];
    }

    private function generateReference(string $prefix): string
    {
        return sprintf('%s-%s-%s', $prefix, (new \DateTimeImmutable())->format('YmdHis'), strtoupper(bin2hex(random_bytes(4))));
    }

    public function computeBreakdown(float $baseAmount): array
    {
        $base = max(0.0, $baseAmount);
        $rate = $this->resolveTaxRate();
        $tax = round($base * $rate, 2);
        $total = round($base + $tax, 2);

        return [
            'base' => number_format($base, 2, '.', ''),
            'tax' => number_format($tax, 2, '.', ''),
            'total' => number_format($total, 2, '.', ''),
            'rate' => $rate,
            'rate_percent' => $rate * 100,
        ];
    }

    private function resolveTaxRate(): float
    {
        $rawRate = trim((string) ($_ENV['PAYMENT_TAX_RATE'] ?? ''));
        if ($rawRate === '') {
            return self::DEFAULT_TAX_RATE;
        }

        $rate = (float) $rawRate;
        if ($rate < 0) {
            $rate = 0.0;
        }
        if ($rate > 1) {
            $rate = $rate / 100;
        }

        return $rate;
    }
}
