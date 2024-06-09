<?php

namespace App\Controller;

use App\Form\DisciplineType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/discipline', name: 'app_admin_discipline_')]
class AdminDisciplineController extends AbstractController
{
    #[Route('/create', name: 'create')]
    public function create(Request $request): Response
    {
        $form = $this->createForm(DisciplineType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('app_admin_discipline_create');
        }

        return $this->render('admin/discipline/create.html.twig', [
            'form' => $form,
        ]);
    }
}
