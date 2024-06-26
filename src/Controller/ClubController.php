<?php

namespace App\Controller;

use App\Repository\ClubRepository;
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

    #[Route('/{slug}', name: 'show')]
    public function show(string $slug, ClubRepository $clubRepository): Response
    {
        return $this->render('club/show.html.twig', [
            'club' => $clubRepository->findOneBy(['slug' => $slug]),
        ]);
    }
}
