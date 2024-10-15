<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Event;
use App\Form\VarSportsMemberEventType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventShowController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/event/{slug}', name: 'app_event_show')]
    public function show(Event $event, Request $request): Response
    {
        $form = $this->createForm(VarSportsMemberEventType::class, $event);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->persist($event);
                $this->entityManager->flush();

                $this->addFlash('success', $this->translator->trans(Message::GENERIC_SUCCESS));
            } catch (\Exception $exception) {
                $this->logger->warning($exception->getMessage());
                $this->addFlash('warning', $this->translator->trans(Message::GENERIC_ERROR));
            }
        }

        return $this->render('event/show.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }
}
