<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccessibilityController extends AbstractController
{
    #[Route('/locale/{locale}', name: 'app_locale', requirements: ['locale' => 'fr|ar|en'])]
    public function setLocale(string $locale, Request $request): Response
    {
        $request->getSession()->set('_locale', $locale);

        return $this->redirect(
            $request->headers->get('referer', '/')
        );
    }

    #[Route('/theme/{theme}', name: 'app_theme', requirements: ['theme' => 'light|dark'])]
    public function setTheme(string $theme, Request $request): JsonResponse
    {
        $request->getSession()->set('theme', $theme);

        return new JsonResponse(['ok' => true]);
    }
}
