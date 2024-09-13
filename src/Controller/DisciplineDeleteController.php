<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Discipline;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class DisciplineDeleteController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
    ) {
    }

    #[Route('/admin/discipline/{id}/delete', name: 'app_admin_discipline_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Discipline $discipline): Response
    {
        try {
            $this->entityManager->remove($discipline);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans(Message::GENERIC_SUCCESS));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->addFlash('error', $this->translator->trans(Message::GENERIC_ERROR));
        }

        return $this->redirectToRoute('app_admin_discipline_dashboard');
    }
}
