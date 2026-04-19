<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminPagesController extends AbstractController
{
    #[Route('/pages', name: 'admin_pages', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/pages.html.twig');
    }
}
