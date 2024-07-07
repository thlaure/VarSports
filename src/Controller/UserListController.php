<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Club;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class UserListController extends AbstractController
{
    public function __construct(
        private Security $security
    ) {
    }
    
    #[Route('/user/list', name: 'app_user_list')]
    #[IsGranted('ROLE_ADMIN_CLUB', message: Message::GENERIC_GRANT_ERROR)]
    public function list(): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new NotFoundResourceException(Message::DATA_NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        if (null === $user->getClub()) {
            $this->addFlash('warning', Message::CLUB_NOT_FOUND);

            return $this->redirectToRoute('app_admin_club_create');
        }

        $users = $user->getClub()->getUsers();

        return $this->render('admin/user/list.html.twig', [
            'users' => $users,
        ]);
    }
}
