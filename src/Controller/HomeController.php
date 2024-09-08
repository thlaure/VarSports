<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\ClubRepository;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('', name: 'app_home_')]
class HomeController extends AbstractController
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private EventRepository $eventRepository,
        private ClubRepository $clubRepository
    ) {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'latest_clubs' => $this->clubRepository->findBy([], ['creationDate' => 'DESC'], 6),
            'latest_articles' => $this->articleRepository->findBy([], ['creationDate' => 'DESC'], 6),
            'latest_events' => $this->eventRepository->findBy([], ['startDate' => 'DESC'], 6),
        ]);
    }
}
