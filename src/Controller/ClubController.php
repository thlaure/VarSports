<?php

namespace App\Controller;

use App\Repository\ClubRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function list(int $page, Request $request): Response
    {
        $term = $request->query->get('term');
        $term = is_string($term) && '' !== trim($term) ? trim($term) : '';

        if (!$term) {
            $clubs = $this->clubRepository->findBy([], ['name' => 'ASC'], $this->nbPerPage, ($page - 1) * $this->nbPerPage);
            $nbResults = $this->clubRepository->count([]);
        } else {
            $allFilteredClubs = $this->clubRepository->findLike('name', $term, ['name' => 'ASC']);
            $clubs = array_slice($allFilteredClubs, ($page - 1) * $this->nbPerPage, $this->nbPerPage);
            $nbResults = count($allFilteredClubs);
        }

        return $this->render('club/list.html.twig', [
            'clubs' => $clubs,
            'nb_pages' => ceil($nbResults / $this->nbPerPage),
            'current_page' => $page,
            'nb_results' => $nbResults,
            'term' => $term,
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
