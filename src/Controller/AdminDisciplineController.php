<?php

namespace App\Controller;

use App\Entity\Discipline;
use App\Form\DisciplineType;
use App\Repository\DisciplineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/discipline', name: 'app_admin_discipline_')]
class AdminDisciplineController extends AbstractController
{
    #[Route('/create', name: 'create')]
    public function create(Request $request, DisciplineRepository $disciplineRepository, EntityManagerInterface $entityManager): Response
    {
        $discipline = new Discipline();
        $form = $this->createForm(DisciplineType::class, $discipline);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $discipline = $form->getData();

            try {
                $entityManager->persist($discipline);
                $entityManager->flush();

                $this->addFlash('success', 'Succès');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue');
            }
            
            return $this->redirectToRoute('app_admin_discipline_create');
        }

        return $this->render('admin/discipline/create.html.twig', [
            'form' => $form,
            'disciplines' => $disciplineRepository->findAll(),
        ]);
    }
}
