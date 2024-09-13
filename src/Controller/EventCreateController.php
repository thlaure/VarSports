<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Event;
use App\Entity\User;
use App\Form\EventType;
use App\Service\FileChecker;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventCreateController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger,
        private FileChecker $fileChecker,
        private FileUploader $fileUploader,
        private TranslatorInterface $translator,
        private MailerInterface $mailer,
        private string $targetDirectory,
    ) {
    }

    #[Route('/admin/event/create', name: 'app_admin_event_create')]
    #[IsGranted('ROLE_MEMBER_CLUB')]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            $this->logger->error($this->translator->trans(Message::DATA_NOT_FOUND), ['user' => $user]);
            throw $this->createNotFoundException();
        }

        if ((!$user instanceof User || !$user->getClub()) && !$this->isGranted('ROLE_ADMIN')) {
            $this->logger->error($this->translator->trans(Message::CLUB_NOT_FOUND), ['user' => $user]);
            throw new \InvalidArgumentException($this->translator->trans(Message::CLUB_NOT_FOUND), Response::HTTP_NOT_FOUND);
        }

        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $event->setAuthor($user);
                $event->setCreationDate(new \DateTimeImmutable());
                $event->setSlug($this->slugger->slug((string) $event->getTitle())->lower());

                if ($user->hasRole('ROLE_ADMIN')) {
                    $event->setValidated(true);
                } else {
                    $event->setClub($user->getClub());
                }

                $this->entityManager->persist($event);
                $this->entityManager->flush();

                $event->setSlug($this->slugger->slug((string) $event->getTitle())->lower().'-'.$event->getId());

                /** @var ?UploadedFile $image */
                $image = $form->get('image')->getData();
                if ($image && $this->fileChecker->checkImageIsValid($image)) {
                    $imageName = $this->fileUploader->upload($image, $this->targetDirectory.'/'.$event->getId());
                    $event->setImage($imageName);
                    $this->entityManager->persist($event);
                }

                $this->entityManager->flush();

                if (!$user->hasRole('ROLE_ADMIN') && is_string($this->getParameter('contact_mail_varsports'))) {
                    $email = (new TemplatedEmail())
                        ->from(new Address('no-reply@varsports.fr', 'VarSports'))
                        ->to($this->getParameter('contact_mail_varsports'))
                        ->subject($this->translator->trans(Message::EMAIL_SUBJECT_CREATE_CLUB))
                        ->htmlTemplate('admin/event/email_validation.html.twig')
                        ->context([
                            'club' => $user->getClub(),
                            'user' => $user,
                            'event' => $event,
                        ]);

                    $this->mailer->send($email);
                }

                $this->addFlash('success', $user->hasRole('ROLE_ADMIN') ? $this->translator->trans(Message::GENERIC_SUCCESS) : $this->translator->trans(Message::SUCCESS_BEFORE_EVENT_VALIDATION));
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->addFlash('error', $this->translator->trans(Message::GENERIC_ERROR));
            }
        }

        return $this->render('admin/event/create_edit.html.twig', [
            'form' => $form,
            'title' => $this->translator->trans(Message::TITLE_CREATE_EVENT),
        ]);
    }
}
