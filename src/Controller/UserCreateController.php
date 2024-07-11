<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\User;
use App\Form\UserEditType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserCreateController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private UserPasswordHasherInterface $userPasswordHasher,
        private EmailVerifier $emailVerifier,
        private Security $security
    ) {
    }

    #[Route('/admin/user/create', name: 'app_user_create')]
    #[IsGranted('ROLE_ADMIN_CLUB', message: Message::GENERIC_GRANT_ERROR)]
    public function create(Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            $this->logger->error(Message::DATA_NOT_FOUND, ['user' => $user]);
            $this->createNotFoundException();
        }

        $userToCreate = new User();

        $form = $this->createForm(UserEditType::class, $userToCreate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if (!is_string($plainPassword) || empty($plainPassword)) {
                $this->logger->error(Message::DATA_MUST_BE_SET, ['user' => $userToCreate]);
                throw new \InvalidArgumentException(Message::DATA_MUST_BE_SET, Response::HTTP_BAD_REQUEST);
            }

            /** @var User $user */
            $userToCreate->setClub($user->getClub());

            $userToCreate->setPassword(
                $this->userPasswordHasher->hashPassword($userToCreate, $plainPassword)
            );

            $this->entityManager->persist($userToCreate);
            $this->entityManager->flush();

            $emailTo = $userToCreate->getEmail();
            if (!is_string($emailTo) || empty($emailTo)) {
                $this->logger->error(Message::DATA_MUST_BE_SET, ['user' => $userToCreate]);
                throw new \InvalidArgumentException(Message::DATA_MUST_BE_SET, Response::HTTP_BAD_REQUEST);
            }

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $userToCreate,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@varsports.fr', 'VarSports'))
                    ->to($emailTo)
                    ->subject(Message::CONFIRM_EMAIL)
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            $this->addFlash('success', Message::CONSULT_MAILBOX_TO_CONFIRM);

            return $this->redirectToRoute('app_user_list');
        }

        return $this->render('admin/user/create_edit.html.twig', [
            'form' => $form,
        ]);
    }
}
