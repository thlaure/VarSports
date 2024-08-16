<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            /** @var User $user */
            $user = $this->getUser();
            if ($user->hasRole('ROLE_ADMIN_CLUB') && null === $user->getClub()) {
                return $this->redirectToRoute('app_admin_club_create');
            }

            return $this->redirectToRoute('app_club_list');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($error) {
            $this->logger->error('Authentication error: '.$error->getMessage());
            $this->addFlash('error', Message::INVALID_CREDENTIALS);
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
    }
}
