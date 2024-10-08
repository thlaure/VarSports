<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Discipline;
use App\Form\DisciplineType;
use App\Repository\DisciplineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface as TranslationTranslatorInterface;

class DisciplineEditController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private DisciplineRepository $disciplineRepository,
        private TranslationTranslatorInterface $translator,
    ) {
    }

    #[Route('/admin/discipline/{id}/edit', name: 'app_admin_discipline_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Discipline $disciplineToEdit, Request $request): Response
    {
        $form = $this->createForm(DisciplineType::class, $disciplineToEdit);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->flush();

                $this->addFlash('success', $this->translator->trans(Message::GENERIC_SUCCESS));
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->addFlash('error', $this->translator->trans(Message::GENERIC_ERROR));
            }
        }

        return $this->render('admin/discipline/dashboard.html.twig', [
            'form' => $form,
            'disciplines' => $this->disciplineRepository->findAll(),
        ]);
    }
}
