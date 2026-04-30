<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class PdfService
{
    public function __construct(
        private Environment $twig
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
        $html = $this->twig->render($template, $data);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('chroot', realpath(__DIR__ . '/../../')); 

        // Performance
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper($paperSize, $orientation);
        $dompdf->render();

        $output = $dompdf->output();

        // Clear any possible output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
