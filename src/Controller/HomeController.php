<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('about.html.twig');
    }

    #[Route('/blog', name: 'blog')]
    public function blog(): Response
    {
        return $this->render('blog.html.twig');
    }

    #[Route('/blog-details', name: 'blog_details')]
    public function blogDetails(): Response
    {
        return $this->render('blog-details.html.twig');
    }

    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('contact.html.twig');
    }

    #[Route('/services', name: 'services')]
    public function services(): Response
    {
        return $this->render('services.html.twig');
    }

    #[Route('/testimonials', name: 'testimonials')]
    public function testimonials(): Response
    {
        return $this->render('testimonials.html.twig');
    }
}