<?php

namespace App\Security;

use App\Constant\Message;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function sendEmailConfirmation(string $verifyEmailRouteName, User $user, TemplatedEmail $email): void
    {
        $userEmail = $user->getEmail();
        if (!is_string($userEmail) || empty($userEmail)) {
            throw new InvalidArgumentException(Message::DATA_MUST_BE_SET, Response::HTTP_BAD_REQUEST);
        }
        
        $signatureComponents = $this->verifyEmailHelper->generateSignature($verifyEmailRouteName, (string) $user->getId(), $userEmail);

        $context = $email->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        $email->context($context);

        $this->mailer->send($email);
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, User $user): void
    {
        $userEmail = $user->getEmail();
        if (!is_string($userEmail) || empty($userEmail)) {
            throw new InvalidArgumentException(Message::DATA_MUST_BE_SET, Response::HTTP_BAD_REQUEST);
        }

        $this->verifyEmailHelper->validateEmailConfirmationFromRequest($request, (string) $user->getId(), $userEmail);

        $user->setVerified(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
