<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Club;
use App\Entity\Event;
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

class EventValidateController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
    ) {
    }

    #[Route('/event/{id}/validate', name: 'app_admin_event_validate')]
    #[IsGranted('ROLE_ADMIN')]
    public function validate(Event $event): Response
    {
        try {
            $event->setValidated(!$event->isValidated());
            $this->entityManager->flush();

            $club = $event->getClub();
            if ($club instanceof Club && is_string($club->getEmail()) && true === $event->isValidated()) {
                $email = (new TemplatedEmail())
                    ->from(new Address('no-reply@varsports.fr', 'VarSports'))
                    ->to($club->getEmail())
                    ->subject($this->translator->trans(Message::EMAIL_SUBJECT_CREATE_CLUB))
                    ->htmlTemplate('admin/event/email_confirm_validation.html.twig')
                    ->context([
                        'event' => $event,
                    ])
                ;

                $this->mailer->send($email);
            } else {
                $this->logger->warning($this->translator->trans(Message::CLUB_NOT_FOUND).': '.$event->getId());
                $this->addFlash('warning', $this->translator->trans(Message::CLUB_NOT_FOUND));
            }

            $this->addFlash('success', $this->translator->trans(Message::GENERIC_SUCCESS));
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->addFlash('error', $this->translator->trans(Message::GENERIC_ERROR));
        }

        return $this->redirectToRoute('app_event_show', ['slug' => $event->getSlug()]);
    }
}
