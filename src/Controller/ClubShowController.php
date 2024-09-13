<?php

namespace App\Controller;

use App\Entity\Club;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ClubShowController extends AbstractController
{
    public function __construct(
        private ArticleRepository $articleRepository,
    ) {
    }

    #[Route('/club/{slug}', name: 'app_club_show')]
    public function show(Club $club): Response
    {
        return $this->render('club/show.html.twig', [
            'club' => $club,
            'articles' => $this->articleRepository->findBy(['club' => $club], ['creationDate' => 'DESC']),
        ]);
    }
}
