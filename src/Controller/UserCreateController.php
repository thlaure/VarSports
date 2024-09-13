<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\User;
use App\Form\UserCreateType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserCreateController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private UserPasswordHasherInterface $userPasswordHasher,
        private TranslatorInterface $translator,
    ) {
    }

    #[Route('/admin/user/create', name: 'app_user_create')]
    #[IsGranted('ROLE_ADMIN_CLUB')]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        if ((!$user instanceof User || !$user->getClub()) && !$this->isGranted('ROLE_ADMIN')) {
            $this->logger->error($this->translator->trans(Message::CLUB_NOT_FOUND), ['user' => $user]);
            throw new \InvalidArgumentException($this->translator->trans(Message::CLUB_NOT_FOUND), Response::HTTP_NOT_FOUND);
        }

        $userToCreate = new User();

        $form = $this->createForm(UserCreateType::class, $userToCreate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if (!is_string($plainPassword) || empty($plainPassword)) {
                $this->logger->error($this->translator->trans(Message::DATA_MUST_BE_SET), ['user' => $userToCreate]);
                throw new \InvalidArgumentException($this->translator->trans(Message::DATA_MUST_BE_SET), Response::HTTP_BAD_REQUEST);
            }

            $user = $this->getUser();
            if ($user instanceof User) {
                if (!$user->hasRole('ROLE_ADMIN')) {
                    $userToCreate->setClub($user->getClub());
                }
            } else {
                $this->logger->error($this->translator->trans(Message::DATA_NOT_FOUND), ['user' => $user]);
                $this->createNotFoundException();
            }

            $userToCreate->setPassword(
                $this->userPasswordHasher->hashPassword($userToCreate, $plainPassword)
            );

            $userToCreate->setVerified(true);

            $this->entityManager->persist($userToCreate);
            $this->entityManager->flush();

            /** @var User $user */
            $successMessage = $user->hasRole('ROLE_ADMIN') ? $this->translator->trans(Message::GENERIC_SUCCESS) : $this->translator->trans(Message::CONSULT_MAILBOX_TO_CONFIRM);
            $this->addFlash('success', $successMessage);

            return $this->redirectToRoute('app_user_list');
        }

        return $this->render('admin/user/create_edit.html.twig', [
            'form' => $form,
        ]);
    }
}
