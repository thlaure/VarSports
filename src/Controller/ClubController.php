<?php

namespace App\Controller;

use App\Constant\Message;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/club', name: 'app_club_')]
class ClubController extends AbstractController
{
    #[Route('/list', name: 'list')]
    public function list(ClubRepository $clubRepository): Response
    {
        $clubs = $clubRepository->findBy([], ['name' => 'ASC']);

        return $this->render('club/list.html.twig', [
            'clubs' => $clubs,
        ]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(int $id, ClubRepository $clubRepository): Response
    {
        return $this->render('club/show.html.twig', [
            'club' => $clubRepository->findOneBy(['id' => $id]),
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
