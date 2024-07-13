<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private LoggerInterface $logger
    ) {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if (!is_string($plainPassword) || empty($plainPassword)) {
                $this->logger->error(Message::DATA_MUST_BE_SET, ['user' => $user]);
                throw new \InvalidArgumentException(Message::DATA_MUST_BE_SET, Response::HTTP_BAD_REQUEST);
            }

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $plainPassword
                )
            );

            $user->setRoles(['ROLE_ADMIN_CLUB']);

            $entityManager->persist($user);
            $entityManager->flush();

            $emailTo = $user->getEmail();
            if (!is_string($emailTo) || empty($emailTo)) {
                $this->logger->error(Message::DATA_MUST_BE_SET, ['user' => $user]);
                throw new \InvalidArgumentException(Message::DATA_MUST_BE_SET, Response::HTTP_BAD_REQUEST);
            }

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@varsports.fr', 'VarSports'))
                    ->to($emailTo)
                    ->subject(Message::CONFIRM_EMAIL)
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            $this->addFlash('success', Message::CONSULT_MAILBOX_TO_CONFIRM);

            return $this->redirectToRoute('app_register');
        }

        return $this->render('registration/register.html.twig', [
            'registration_form' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    #[IsGranted('IS_AUTHENTICATED_FULLY', message: Message::GENERIC_GRANT_ERROR)]
    public function verifyUserEmail(Request $request): Response
    {
        try {
            $user = $this->getUser();

            if (!$user instanceof User) {
                $this->logger->error(Message::ERROR_WHILE_CONFIRM_EMAIL, ['user' => $user]);
                throw new NotFoundResourceException(Message::ERROR_WHILE_CONFIRM_EMAIL, Response::HTTP_BAD_REQUEST);
            }

            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->logger->error($exception->getReason());
            $this->addFlash('verify_email_error', Message::ERROR_WHILE_CONFIRM_EMAIL);

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', Message::EMAIL_VERIFIED);

        return $this->redirectToRoute('app_login');
    }
}
