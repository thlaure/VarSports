<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Club;
use App\Entity\User;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

#[Route('/admin/club', name: 'app_admin_club_')]
class ClubDeleteController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private ClubRepository $clubRepository
    ) {
    }

    #[Route('/{id}/delete', name: 'delete')]
    #[IsGranted('ROLE_ADMIN_CLUB', message: Message::GENERIC_GRANT_ERROR)]
    public function delete(int $id): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            $this->logger->error(Message::DATA_NOT_FOUND, ['user' => $user]);
            throw new NotFoundResourceException(Message::DATA_NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        $club = $this->clubRepository->findOneBy(['id' => $id]);
        if (!$club instanceof Club) {
            $this->logger->error(Message::DATA_NOT_FOUND, ['club' => $club]);
            throw new NotFoundResourceException(Message::DATA_NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        if (
            ($user->hasRole('ROLE_ADMIN_CLUB') && $user->getClub() && $user->getClub()->getId() !== $club->getId())
            || (!$user->hasRole('ROLE_ADMIN_CLUB') && !$user->hasRole('ROLE_ADMIN'))
        ) {
            $this->logger->error(Message::GENERIC_ACCESS_DENIED, ['user' => $user]);
            throw new AccessDeniedHttpException();
        }

        try {
            $this->entityManager->remove($club);
            $this->entityManager->flush();

            $this->addFlash('success', Message::GENERIC_SUCCESS);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->addFlash('error', Message::GENERIC_ERROR.' '.$e->getMessage());
        }

        return $this->redirectToRoute('app_club_list');
    }
}
