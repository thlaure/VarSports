<?php

namespace App\Controller;

use App\Constant\Message;
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
    #[Route('/dashboard', name: 'dashboard')]
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

                $this->addFlash('success', Message::GENERIC_SUCCESS);
            } catch (\Exception $e) {
                $this->addFlash('error', Message::GENERIC_ERROR);
            }
            
            return $this->redirectToRoute('app_admin_discipline_dashboard');
        }

        return $this->render('admin/discipline/dashboard.html.twig', [
            'form' => $form,
            'disciplines' => $disciplineRepository->findAll(),
        ]);
    }

    #[Route('/{id}/delete', name: 'delete')]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        $discipline = $entityManager->getRepository(Discipline::class)->find($id);
        
        try {
            $entityManager->remove($discipline);
            $entityManager->flush();

            $this->addFlash('success', Message::GENERIC_SUCCESS);
        } catch (\Exception $e) {
            $this->addFlash('error', Message::GENERIC_ERROR);
        }

        return $this->redirectToRoute('app_admin_discipline_dashboard');
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(int $id, DisciplineRepository $disciplineRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $disciplineToEdit = $disciplineRepository->find($id);
        $form = $this->createForm(DisciplineType::class, $disciplineToEdit);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();

                $this->addFlash('success', Message::GENERIC_SUCCESS);
            } catch (\Exception $e) {
                $this->addFlash('error', Message::GENERIC_ERROR);
            }

            return $this->redirectToRoute('app_admin_discipline_dashboard');
        }

        return $this->render('admin/discipline/dashboard.html.twig', [
            'form' => $form,
            'disciplines' => $disciplineRepository->findAll(),
        ]);
    }
}
