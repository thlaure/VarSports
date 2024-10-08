<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\User;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserListController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private UserRepository $userRepository,
        private TranslatorInterface $translator,
    ) {
    }

    #[Route('/admin/user/list', name: 'app_user_list')]
    #[IsGranted('ROLE_ADMIN_CLUB')]
    public function list(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            $this->logger->error($this->translator->trans(Message::GENERIC_ERROR), ['user' => $user]);
            throw new NotFoundResourceException($this->translator->trans(Message::DATA_NOT_FOUND), Response::HTTP_NOT_FOUND);
        }

        if ($user->hasRole('ROLE_ADMIN')) {
            return $this->render('admin/user/list.html.twig', [
                'users' => $this->userRepository->findBy([], ['id' => 'DESC']),
            ]);
        }

        if (null === $user->getClub()) {
            $this->addFlash('warning', $this->translator->trans(Message::CLUB_NOT_FOUND));

            return $this->redirectToRoute('app_admin_club_create');
        }

        $users = $user->getClub()->getUsers();

        return $this->render('admin/user/list.html.twig', [
            'users' => $users,
        ]);
    }
}
