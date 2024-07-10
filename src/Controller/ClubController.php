<?php

namespace App\Controller;

use App\Entity\Discipline;
use App\Repository\ClubRepository;
use App\Repository\DisciplineRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/club', name: 'app_club_')]
class ClubController extends AbstractController
{
    public function __construct(
        private ClubRepository $clubRepository,
        private DisciplineRepository $disciplineRepository,
        private int $nbPerPage
    ) {
    }

    #[Route('/list/{page<\d+>?1}', name: 'list')]
    public function list(int $page, Request $request): Response
    {
        $searchParams = $request->query->all();

        if (empty($searchParams)) {
            $clubs = $this->clubRepository->findBy([], ['name' => 'ASC'], $this->nbPerPage, ($page - 1) * $this->nbPerPage);
            $nbResults = $this->clubRepository->count([]);
        } else {
            $term = isset($searchParams['term']) && is_string($searchParams['term']) && '' !== trim($searchParams['term']) ? trim($searchParams['term']) : '';
            $disciplinesId = isset($searchParams['disciplines']) && is_array($searchParams['disciplines']) ? $searchParams['disciplines'] : [];
            $disciplines = [];
            foreach ($disciplinesId as $id) {
                $discipline = $this->disciplineRepository->find($id);
                if ($discipline instanceof Discipline) {
                    $disciplines[] = $discipline;
                }
            }

            $allFilteredClubs = $this->clubRepository->searchClub($term, $disciplines, ['name' => 'ASC']);
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
            'selected_disciplines' => $disciplinesId ?? [],
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
