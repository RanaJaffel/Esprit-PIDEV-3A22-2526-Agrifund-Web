<?php

namespace App\Controller\Banque;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/banque')]
#[IsGranted('ROLE_BANQUE')]
class BanquePagesController extends AbstractController
{
    #[Route('/pages', name: 'banque_pages', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('banque/pages.html.twig');
    }
}
