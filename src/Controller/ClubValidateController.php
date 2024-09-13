<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Club;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class ClubValidateController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
    ) {
    }

    #[Route('/club/{id}/validate', name: 'app_admin_club_validate')]
    #[IsGranted('ROLE_ADMIN')]
    public function validate(Club $club): Response
    {
        try {
            $club->setValidated(!$club->isValidated());
            $this->entityManager->flush();

            if (is_string($club->getEmail()) && true === $club->isValidated()) {
                $email = (new TemplatedEmail())
                    ->from(new Address('no-reply@varsports.fr', 'VarSports'))
                    ->to($club->getEmail())
                    ->subject($this->translator->trans(Message::EMAIL_SUBJECT_CREATE_CLUB))
                    ->htmlTemplate('admin/club/email_confirm_validation.html.twig')
                    ->context([
                        'club' => $club,
                    ])
                ;

                $this->mailer->send($email);
            } else {
                $this->logger->warning($this->translator->trans(Message::ERROR_CLUB_HAS_NO_EMAIL).': '.$club->getName());
                $this->addFlash('warning', $this->translator->trans(Message::ERROR_CLUB_HAS_NO_EMAIL));
            }

            $this->addFlash('success', $this->translator->trans(Message::GENERIC_SUCCESS));
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->addFlash('error', $this->translator->trans(Message::GENERIC_ERROR));
        }

        return $this->redirectToRoute('app_club_show', ['slug' => $club->getSlug()]);
    }
}
