<?php

namespace App\Controller;

use App\Entity\Discipline;
use App\Repository\CityRepository;
use App\Repository\ClubRepository;
use App\Repository\DisciplineRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ClubListController extends AbstractController
{
    public function __construct(
        private ClubRepository $clubRepository,
        private DisciplineRepository $disciplineRepository,
        private CityRepository $cityRepository,
        private int $nbPerPage,
    ) {
    }

    #[Route('/club/list/{page<\d+>?1}', name: 'app_club_list')]
    public function list(int $page, Request $request): Response
    {
        $searchParams = $request->query->all();

        if (empty($searchParams)) {
            $clubs = $this->clubRepository->findBy(['isValidated' => true], ['name' => 'ASC'], $this->nbPerPage, ($page - 1) * $this->nbPerPage);
            $nbResults = $this->clubRepository->count(['isValidated' => true]);
        } else {
            $term = isset($searchParams['term']) && is_string($searchParams['term']) && '' !== trim($searchParams['term']) ? trim($searchParams['term']) : '';

            $selectedDisciplinesId = isset($searchParams['disciplines']) && is_array($searchParams['disciplines']) ? $searchParams['disciplines'] : [];
            $selectedDisciplines = [];
            foreach ($selectedDisciplinesId as $id) {
                $discipline = $this->disciplineRepository->find($id);
                if ($discipline instanceof Discipline) {
                    $selectedDisciplines[] = $discipline;
                }
            }

            $selectedCitiesId = isset($searchParams['cities']) && is_array($searchParams['cities']) ? $searchParams['cities'] : [];
            $selectedCities = [];
            foreach ($selectedCitiesId as $city) {
                $selectedCities[] = $city;
            }

            $allFilteredClubs = $this->clubRepository->searchClub($term, $selectedDisciplines, $selectedCities, ['name' => 'ASC']);
            $clubs = array_slice($allFilteredClubs, ($page - 1) * $this->nbPerPage, $this->nbPerPage);
            $nbResults = count($allFilteredClubs);
        }

        return $this->render('club/list.html.twig', [
            'clubs' => $clubs,
            'nb_pages' => 0 < ceil($nbResults / $this->nbPerPage) ? ceil($nbResults / $this->nbPerPage) : 1,
            'current_page' => $page,
            'nb_results' => $nbResults,
            'term' => $term ?? null,
            'disciplines' => $this->disciplineRepository->findBy([], ['label' => 'ASC']),
            'selected_disciplines' => $selectedDisciplinesId ?? [],
            'cities' => $this->cityRepository->findBy([], ['name' => 'ASC']),
            'selected_cities' => $selectedCities ?? [],
        ]);
    }
}
