<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class UserDeleteController extends AbstractController
{
    public function __construct(
        private Security $security,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {
    }

    #[Route('/admin/user/delete/{id}', name: 'app_user_delete')]
    #[IsGranted('ROLE_ADMIN_CLUB', message: Message::GENERIC_GRANT_ERROR)]
    public function delete(int $id): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            $this->logger->error(Message::DATA_NOT_FOUND, ['user' => $user]);
            throw new NotFoundResourceException(Message::DATA_NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        $userToDelete = $this->userRepository->findOneBy(['id' => $id]);
        if (!$userToDelete instanceof User) {
            $this->logger->error(Message::DATA_NOT_FOUND, ['user' => $user]);
            throw new NotFoundResourceException(Message::DATA_NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        if ($userToDelete->hasRole('ROLE_ADMIN') || $user->getId() === $userToDelete->getId()) {
            $this->logger->error(Message::GENERIC_GRANT_ERROR, ['user' => $user]);
            throw new AccessDeniedHttpException();
        }

        if (null === $user->getClub()) {
            $this->addFlash('warning', Message::CLUB_NOT_FOUND);

            return $this->redirectToRoute('app_admin_club_create');
        }

        try {
            $this->entityManager->remove($userToDelete);
            $this->entityManager->flush();

            $this->addFlash('success', Message::GENERIC_SUCCESS);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->addFlash('error', Message::GENERIC_ERROR);
        }

        return $this->redirectToRoute('app_user_list');
    }
}
