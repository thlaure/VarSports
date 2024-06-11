<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Club;
use App\Form\ClubType;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/club', name: 'app_admin_club_')]
class AdminClubController extends AbstractController
{
    #[Route('/create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $club = new Club();
        $form = $this->createForm(ClubType::class, $club);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($club);
                $entityManager->flush();

                $this->addFlash('success', Message::GENERIC_SUCCESS);

                return $this->redirectToRoute('app_club_list');
            } catch (\Exception $e) {
                $this->addFlash('error', Message::GENERIC_ERROR);
            }
        }

        return $this->render('admin/club/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'delete')]
    public function delete(int $id, ClubRepository $clubRepository, EntityManagerInterface $entityManager): Response
    {
        $club = $clubRepository->findOneBy(['id' => $id]);

        try {
            $entityManager->remove($club);
            $entityManager->flush();

            $this->addFlash('success', Message::GENERIC_SUCCESS);
        } catch (\Exception $e) {
            $this->addFlash('error', Message::GENERIC_ERROR);
        }

        return $this->redirectToRoute('app_club_list');
    }
}
