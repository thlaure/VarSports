<?php

namespace App\Controller;

use App\Repository\ClubRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/club', name: 'app_club_')]
class ClubController extends AbstractController
{
    public function __construct(
        private ClubRepository $clubRepository,
        private int $nbPerPage
    ) {
    }

    #[Route('/list/{page<\d+>?1}', name: 'list')]
    public function list(int $page): Response
    {
        $clubs = $this->clubRepository->findBy([], ['name' => 'ASC'], $this->nbPerPage, ($page - 1) * 12);

        $nbPages = ceil($this->clubRepository->count([]) / $this->nbPerPage);

        return $this->render('club/list.html.twig', [
            'clubs' => $clubs,
            'nb_pages' => $nbPages,
            'current_page' => $page,
        ]);
    }

    #[Route('/{slug}', name: 'show')]
    public function show(string $slug): Response
    {
        return $this->render('club/show.html.twig', [
            'club' => $this->clubRepository->findOneBy(['slug' => $slug]),
        ]);
    }
}
