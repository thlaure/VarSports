<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserDeleteController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private TranslatorInterface $translator,
    ) {
    }

    #[Route('/admin/user/delete/{id}', name: 'app_user_delete')]
    #[IsGranted('ROLE_ADMIN_CLUB')]
    public function delete(User $userToDelete): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            $this->logger->error($this->translator->trans(Message::DATA_NOT_FOUND), ['user' => $user]);
            throw new NotFoundResourceException($this->translator->trans(Message::DATA_NOT_FOUND), Response::HTTP_NOT_FOUND);
        }

        if ($userToDelete->hasRole('ROLE_ADMIN') || $user->getId() === $userToDelete->getId()) {
            $this->logger->error($this->translator->trans(Message::GENERIC_GRANT_ERROR), ['user' => $user]);
            throw new AccessDeniedHttpException();
        }

        if (null === $user->getClub()) {
            $this->addFlash('warning', $this->translator->trans(Message::CLUB_NOT_FOUND));

            return $this->redirectToRoute('app_admin_club_create');
        }

        try {
            $this->entityManager->remove($userToDelete);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans(Message::GENERIC_SUCCESS));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->addFlash('error', $this->translator->trans(Message::GENERIC_ERROR));
        }

        return $this->redirectToRoute('app_user_list');
    }
}
