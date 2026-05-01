<?php

namespace App\Service;

use Nucleos\DompdfBundle\Factory\DompdfFactoryInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Environment;

class PdfService
{
    public function __construct(
        private Environment $twig,
        private DompdfFactoryInterface $dompdfFactory,
        private Security $security
    ) {}

    /**
     * Génère un PDF à partir d'un template Twig et retourne une Response
     */
    public function generatePdfResponse(
        string $template,
        array $data = [],
        string $filename = 'document.pdf',
        string $paperSize = 'A4',
        string $orientation = 'portrait'
    ): Response {
        $data = $this->withPersonalizedContext($template, $data);
        $html = $this->twig->render($template, $data);

        $chroot = realpath(__DIR__ . '/../../') ?: (__DIR__ . '/../../');
        $dompdf = $this->dompdfFactory->create([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
            'chroot' => $chroot,
        ]);

        // Performance
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper($paperSize, $orientation);
        $dompdf->render();

        $output = $dompdf->output();

        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    /**
     * Adds reusable personalization fields for all Dompdf bundle exports.
     */
    private function withPersonalizedContext(string $template, array $data): array
    {
        $user = $this->security->getUser();
        $generatedAt = $data['generatedAt'] ?? $data['date'] ?? new \DateTimeImmutable();

        if (!$generatedAt instanceof \DateTimeInterface) {
            $generatedAt = new \DateTimeImmutable();
        }

        $context = [
            'brand_name' => 'AgriFund',
            'brand_email' => 'contact@agrifund.tn',
            'brand_phone' => '+216 71 000 000',
            'brand_address' => 'Avenue Habib Bourguiba, Tunis',
            'generated_at' => $generatedAt,
            'generated_by_name' => $this->resolveUserName($user),
            'generated_by_email' => $this->resolveUserEmail($user),
            'generated_by_role' => $this->resolveUserRole($user),
            'document_reference' => $this->buildReference($template, $generatedAt),
        ];
        $context = array_replace($context, $data['pdf_context'] ?? []);

        return array_replace([
            'date' => $generatedAt,
            'generatedAt' => $generatedAt,
            'client_name' => $context['generated_by_name'],
            'client_email' => $context['generated_by_email'],
            'pdf_context' => $context,
        ], $data);
    }

    private function resolveUserName(?UserInterface $user): string
    {
        if ($user === null) {
            return 'Visiteur AgriFund';
        }

        if (method_exists($user, 'getNomComplet')) {
            $name = trim((string) $user->getNomComplet());
            if ($name !== '') {
                return $name;
            }
        }

        if (method_exists($user, 'getFirstname') && method_exists($user, 'getLastname')) {
            $name = trim((string) $user->getFirstname() . ' ' . (string) $user->getLastname());
            if ($name !== '') {
                return $name;
            }
        }

        return $user->getUserIdentifier();
    }

    private function resolveUserEmail(?UserInterface $user): ?string
    {
        if ($user === null) {
            return null;
        }

        if (method_exists($user, 'getEmail')) {
            $email = trim((string) $user->getEmail());
            if ($email !== '') {
                return $email;
            }
        }

        return $user->getUserIdentifier();
    }

    private function resolveUserRole(?UserInterface $user): string
    {
        if ($user === null) {
            return 'Invite';
        }

        if (method_exists($user, 'getTypeUtilisateur')) {
            $role = trim((string) $user->getTypeUtilisateur());
            if ($role !== '') {
                return $role;
            }
        }

        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            return 'Admin';
        }

        if (in_array('ROLE_BANQUE', $roles, true)) {
            return 'Banque';
        }

        if (in_array('ROLE_AGRICULTEUR', $roles, true)) {
            return 'Agriculteur';
        }

        return 'Utilisateur';
    }

    private function buildReference(string $template, \DateTimeInterface $generatedAt): string
    {
        $base = basename($template);
        $base = preg_replace('/\.html\.twig$|\.twig$/', '', $base) ?: 'pdf';
        $base = strtoupper($base);
        $base = preg_replace('/[^A-Z0-9]+/', '-', $base) ?: 'PDF';

        return sprintf('%s-%s', trim($base, '-'), $generatedAt->format('Ymd-His'));
    }
}
