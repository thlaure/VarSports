<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Event;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventDeleteController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private TranslatorInterface $translator,
    ) {
    }

    #[Route('/admin/event/{id}/delete', name: 'app_admin_event_delete')]
    #[IsGranted('ROLE_ADMIN_CLUB')]
    public function delete(Event $event): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->hasRole('ROLE_ADMIN')) {
            $this->logger->error($this->translator->trans(Message::GENERIC_ACCESS_DENIED), ['user' => $user]);
            throw new AccessDeniedHttpException();
        }

        try {
            $this->entityManager->remove($event);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans(Message::GENERIC_SUCCESS));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->addFlash('error', $this->translator->trans(Message::GENERIC_ERROR).' '.$e->getMessage());
        }

        return $this->redirectToRoute('app_event_list');
    }
}
