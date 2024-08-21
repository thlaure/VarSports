<?php

namespace App\Controller;

use App\Entity\Club;
use App\Repository\ArticleRepository;
use App\Repository\ClubRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/club', name: 'app_club_')]
class ClubShowController extends AbstractController
{
    public function __construct(
        private ClubRepository $clubRepository,
        private ArticleRepository $articleRepository
    ) {
    }

    #[Route('/{slug}', name: 'show')]
    public function show(string $slug): Response
    {
        $club = $this->clubRepository->findOneBy(['slug' => $slug]);
        if (!$club instanceof Club) {
            throw $this->createNotFoundException();
        }

        return $this->render('club/show.html.twig', [
            'club' => $club,
            'articles' => $this->articleRepository->findBy(['club' => $club], ['creationDate' => 'DESC']),
        ]);
    }
}
