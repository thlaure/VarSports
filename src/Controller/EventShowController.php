<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EventShowController extends AbstractController
{
    public function __construct(
        private EventRepository $eventRepository
    ) {
    }

    #[Route('/event/{slug}', name: 'app_event_show')]
    public function show(string $slug): Response
    {
        $event = $this->eventRepository->findOneBy(['slug' => $slug]);
        if (!$event instanceof Event) {
            throw $this->createNotFoundException();
        }

        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }
}
