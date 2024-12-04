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

        return $this->render('home/index.html.twig', [
            'latest_articles' => $this->articleRepository->findBy([], ['creationDate' => 'DESC'], 6),
            'profile' => $this->articleRepository->findOneBy(['homeCategory' => $profileCategory], ['creationDate' => 'DESC']),
        ]);
    }
}
