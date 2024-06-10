<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminClubController extends AbstractController
{
    #[Route('/admin/club', name: 'app_admin_club')]
    public function index(): Response
    {
        return $this->render('admin_club/index.html.twig', [
            'controller_name' => 'AdminClubController',
        ]);
    }
}
