<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EventListController extends AbstractController
{
    public function __construct(
        private EventRepository $eventRepository,
        private int $nbPerPage
    ) {
    }
    #[Route('/event/list/{page<\d+>?1}', name: 'app_event_list')]
    public function list(int $page): Response
    {
        $events = $this->eventRepository->findBy([], ['creationDate' => 'DESC'], $this->nbPerPage, ($page - 1) * $this->nbPerPage);
        $nbResults = $this->eventRepository->count([]);

        return $this->render('event/list.html.twig', [
            'events' => $events,
            'nb_results' => $nbResults,
            'nb_pages' => 0 < ceil($nbResults / $this->nbPerPage) ? ceil($nbResults / $this->nbPerPage) : 1,
            'current_page' => $page,
        ]);
    }
}
