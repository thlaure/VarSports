<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\City;
use App\Entity\Event;
use App\Entity\User;
use App\Form\EventType;
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
use Symfony\Contracts\Translation\TranslatorInterface;

class EventEditController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger,
        private FileChecker $fileChecker,
        private FileUploader $fileUploader,
        private TranslatorInterface $translator,
        private string $targetDirectory,
    ) {
    }

    #[Route('/admin/event/{id}/edit', name: 'app_admin_event_edit')]
    #[IsGranted('ROLE_MEMBER_CLUB')]
    public function edit(Event $event, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $event->setAuthor($user);
                $event->setCreationDate(new \DateTimeImmutable());
                $event->setSlug($this->slugger->slug((string) $event->getTitle())->lower());

                if (null !== $event->getCity()) {
                    $existingCity = $this->entityManager->getRepository(City::class)->findOneBy([
                        'name' => $event->getCity()->getName(),
                        'postalCode' => $event->getCity()->getPostalCode(),
                    ]);

                    if (null !== $existingCity) {
                        $event->setCity($existingCity);
                    } else {
                        $this->entityManager->persist($event->getCity());
                    }
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

                $event->setLastUpdate(new \DateTime());

                $this->entityManager->flush();

                $this->addFlash('success', $this->translator->trans(Message::GENERIC_SUCCESS));

                return $this->redirectToRoute('app_event_show', ['slug' => $event->getSlug()]);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->addFlash('error', $this->translator->trans(Message::GENERIC_ERROR));
            }
        }

        return $this->render('admin/event/create_edit.html.twig', [
            'form' => $form,
            'title' => $this->translator->trans(Message::TITLE_EDIT_EVENT),
        ]);
    }
}
