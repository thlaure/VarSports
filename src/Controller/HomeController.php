<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private EventRepository $eventRepository,
    ) {
    }

    #[Route('/', name: 'app_home_index')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'latest_articles' => $this->articleRepository->findBy([], ['creationDate' => 'DESC'], 6),
            'latest_events' => $this->eventRepository->findBy(['isValidated' => true], ['startDate' => 'DESC'], 6),
        ]);
    }
}
