<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\HomeCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private HomeCategoryRepository $homeCategoryRepository,
    ) {
    }

    #[Route('/', name: 'app_home_index')]
    public function index(): Response
    {
        $profileCategory = $this->homeCategoryRepository->findOneBy(['label' => 'profile']);
        $toNoteCategory = $this->homeCategoryRepository->findOneBy(['label' => 'to_note']);
        $spotlightCategory = $this->homeCategoryRepository->findOneBy(['label' => 'spotlight']);
        $socialCategory = $this->homeCategoryRepository->findOneBy(['label' => 'social']);

        return $this->render('home/index.html.twig', [
            'profile' => $this->articleRepository->findOneBy(['homeCategory' => $profileCategory], ['creationDate' => 'DESC']),
            'articles_to_note' => $this->articleRepository->findBy(['homeCategory' => $toNoteCategory], ['creationDate' => 'DESC'], 5),
            'articles_spotlight' => $this->articleRepository->findBy(['homeCategory' => $spotlightCategory], ['creationDate' => 'DESC'], 5),
            'articles_social' => $this->articleRepository->findBy(['homeCategory' => $socialCategory], ['creationDate' => 'DESC'], 10),
        ]);
    }
}
