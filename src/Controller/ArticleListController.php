<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticleListController extends AbstractController
{
    public function __construct(
        private int $nbPerPage,
        private ArticleRepository $articleRepository,
    ) {
    }

    #[Route('/article/list/{page<\d+>?1}', name: 'app_article_list')]
    public function list(int $page): Response
    {
        $articles = $this->articleRepository->findBy([], ['creationDate' => 'DESC'], $this->nbPerPage, ($page - 1) * $this->nbPerPage);
        $nbResults = $this->articleRepository->count([]);

        return $this->render('article/list.html.twig', [
            'articles' => $articles,
            'nb_results' => $nbResults,
            'nb_pages' => 0 < ceil($nbResults / $this->nbPerPage) ? ceil($nbResults / $this->nbPerPage) : 1,
            'current_page' => $page,
        ]);
    }
}
