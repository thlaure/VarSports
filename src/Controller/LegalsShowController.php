<?php

namespace App\Controller;

use App\Repository\LegalsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegalsShowController extends AbstractController
{
    public function __construct(
        private LegalsRepository $legalsRepository,
    ) {
    }

    #[Route('/legals/{slug}', name: 'app_legals_show')]
    public function show(string $slug): Response
    {
        return $this->render('legals/show.html.twig', [
            'legals' => $this->legalsRepository->findOneBy(['slug' => $slug]),
        ]);
    }
}
