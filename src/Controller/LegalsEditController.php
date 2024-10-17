<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Legals;
use App\Form\LegalsType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class LegalsEditController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private TranslatorInterface $translator,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/legals/{id}/edit', name: 'app_legals_edit')]
    public function edit(Legals $legals, Request $request): Response
    {
        $form = $this->createForm(LegalsType::class, $legals);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $legals->setLastUpdate(new \DateTime());
                $this->entityManager->persist($legals);
                $this->entityManager->flush();

                $this->addFlash('success', $this->translator->trans(Message::GENERIC_SUCCESS));
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->addFlash('error', $this->translator->trans(Message::GENERIC_ERROR));
            }
        }

        return $this->render('admin/legals/edit.html.twig', [
            'form' => $form->createView(),
            'title' => $legals->getTitle(),
        ]);
    }
}
