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
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/discipline', name: 'app_admin_discipline_')]
class DisciplineDeleteController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator
    ) {
    }

    #[Route('/{id}/delete', name: 'delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id): Response
    {
        $discipline = $this->entityManager->getRepository(Discipline::class)->find($id);

        try {
            if (!$discipline instanceof Discipline) {
                $this->logger->error($this->translator->trans(Message::DATA_NOT_FOUND), ['discipline' => $discipline]);
                throw new NotFoundResourceException($this->translator->trans(Message::DATA_NOT_FOUND), Response::HTTP_NOT_FOUND);
            }

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
