<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Event;
use App\Entity\User;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Service\FileChecker;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class EventEditController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private EventRepository $eventRepository,
        private SluggerInterface $slugger,
        private FileChecker $fileChecker,
        private FileUploader $fileUploader,
        private string $targetDirectory
    ) {
    }

    #[Route('/admin/event/{id}/edit', name: 'app_admin_event_edit')]
    #[IsGranted('ROLE_ADMIN', message: Message::GENERIC_GRANT_ERROR)]
    public function edit(int $id, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            $this->logger->error(Message::DATA_NOT_FOUND, ['user' => $user]);
            throw $this->createNotFoundException();
        }

        $event = $this->eventRepository->findOneBy(['id' => $id]);
        if (!$event instanceof Event) {
            $this->logger->error(Message::DATA_NOT_FOUND, ['event' => $event]);
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $event->setAuthor($user);
                $event->setCreationDate(new \DateTimeImmutable());
                $event->setSlug($this->slugger->slug((string) $event->getTitle())->lower());

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

                $event->setLastUpdate(new \DateTime());

                $this->entityManager->flush();

                $this->addFlash('success', Message::GENERIC_SUCCESS);

                return $this->redirectToRoute('app_event_show', ['slug' => $event->getSlug()]);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->addFlash('error', Message::GENERIC_ERROR);
            }
        }

        return $this->render('admin/event/create_edit.html.twig', [
            'form' => $form,
            'title' => Message::TITLE_EDIT_EVENT,
        ]);
    }
}
