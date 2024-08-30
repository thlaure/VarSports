<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Club;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ClubValidateController extends AbstractController
{
    public function __construct(
        private ClubRepository $clubRepository,
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer
    ) {
    }

    #[Route('/club/{id}/validate', name: 'app_admin_club_validate')]
    #[IsGranted('ROLE_ADMIN', message: Message::GENERIC_GRANT_ERROR)]
    public function validate(int $id): Response
    {
        $club = $this->clubRepository->findOneBy(['id' => $id]);
        if (!$club instanceof Club) {
            $this->logger->error(Message::DATA_NOT_FOUND, ['club' => $club]);
            throw $this->createNotFoundException();
        }

        try {
            $club->setValidated(!$club->isValidated());
            $this->entityManager->flush();

            if (is_string($club->getEmail())) {
                $email = (new TemplatedEmail())
                    ->from(new Address('no-reply@varsports.fr', 'VarSports'))
                    ->to($club->getEmail())
                    ->subject('Demande de création de club')
                    ->htmlTemplate('admin/club/email_confirm_validation.html.twig')
                    ->context([
                        'club' => $club,
                    ])
                ;

                $this->mailer->send($email);
            } else {
                $this->logger->warning('Club has no email: '.$club->getName());
                $this->addFlash('warning', "Le club n'a pas d'e-mail");
            }

            $this->addFlash('success', Message::GENERIC_SUCCESS);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->addFlash('error', Message::GENERIC_ERROR);
        }

        return $this->redirectToRoute('app_club_show', ['slug' => $club->getSlug()]);
    }
}
