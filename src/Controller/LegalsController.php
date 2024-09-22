<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegalsController extends AbstractController
{
    #[Route('/mentions-legales', name: 'app_legals_mentions')]
    public function legalMentions(): Response
    {
        return $this->render('legals/legals_mentions.html.twig');
    }

    #[Route('/cgu', name: 'app_legals_conditions_use')]
    public function conditionsUse(): Response
    {
        return $this->render('legals/conditions_use.html.twig');
    }

    #[Route('/cgv', name: 'app_legals_conditions_sales')]
    public function conditionsSales(): Response
    {
        return $this->render('legals/conditions_sales.html.twig');
    }

    #[Route('/confidentialite', name: 'app_legals_privacy_policy')]
    public function privacyPolicy(): Response
    {
        return $this->render('legals/privacy_policy.html.twig');
    }
}
