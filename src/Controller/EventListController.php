<?php

namespace App\Controller;

use App\Entity\Club;
use App\Repository\CityRepository;
use App\Repository\ClubRepository;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EventListController extends AbstractController
{
    public function __construct(
        private EventRepository $eventRepository,
        private ClubRepository $clubRepository,
        private CityRepository $cityRepository,
        private int $nbPerPage,
    ) {
    }

    #[Route('/event/list/{page<\d+>?1}', name: 'app_event_list')]
    public function list(int $page, Request $request): Response
    {
        // $events = $this->eventRepository->findBy(['isValidated' => true], ['creationDate' => 'DESC'], $this->nbPerPage, ($page - 1) * $this->nbPerPage);
        // $nbResults = $this->eventRepository->count([]);

        $searchParams = $request->query->all();

        if (empty($searchParams)) {
            $events = $this->eventRepository->findBy(['isValidated' => true], ['title' => 'ASC', 'startDate' => 'DESC'], $this->nbPerPage, ($page - 1) * $this->nbPerPage);
            $nbResults = $this->eventRepository->count(['isValidated' => true]);
        } else {
            $term = isset($searchParams['term']) && is_string($searchParams['term']) && '' !== trim($searchParams['term']) ? trim($searchParams['term']) : '';

            $selectedClubsId = isset($searchParams['clubs']) && is_array($searchParams['clubs']) ? $searchParams['clubs'] : [];
            $selectedClubs = [];
            foreach ($selectedClubsId as $id) {
                $club = $this->clubRepository->find($id);
                if ($club instanceof Club) {
                    $selectedClubs[] = $club;
                }
            }

            $selectedCitiesId = isset($searchParams['cities']) && is_array($searchParams['cities']) ? $searchParams['cities'] : [];
            $selectedCities = [];
            foreach ($selectedCitiesId as $city) {
                $selectedCities[] = $city;
            }

            $allFilteredEvents = $this->eventRepository->searchEvent($term, $selectedClubs, $selectedCities, ['title' => 'ASC', 'startDate' => 'DESC']);
            $events = array_slice($allFilteredEvents, ($page - 1) * $this->nbPerPage, $this->nbPerPage);
            $nbResults = count($allFilteredEvents);
        }

        return $this->render('event/list.html.twig', [
            'events' => $events,
            'nb_pages' => 0 < ceil($nbResults / $this->nbPerPage) ? ceil($nbResults / $this->nbPerPage) : 1,
            'current_page' => $page,
            'nb_results' => $nbResults,
            'term' => $term ?? null,
            'clubs' => $this->clubRepository->findBy([], ['name' => 'ASC']),
            'selected_clubs' => $selectedClubsId ?? [],
            'cities' => $this->cityRepository->findBy([], ['name' => 'ASC']),
            'selected_cities' => $selectedCities ?? [],
        ]);
    }
}
