<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\User;
use App\Form\UserEditType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserEditController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private TranslatorInterface $translator,
    ) {
    }

    #[Route('/admin/user/{id}/edit', name: 'app_user_edit')]
    #[IsGranted('ROLE_ADMIN_CLUB')]
    public function edit(User $user, Request $request): Response
    {
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $user->setLastUpdateDate(new \DateTimeImmutable());

                $this->entityManager->flush();
                $this->addFlash('success', $this->translator->trans(Message::GENERIC_SUCCESS));

                return $this->redirectToRoute('app_user_list');
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->addFlash('error', $this->translator->trans(Message::GENERIC_ERROR));

                return $this->redirectToRoute('app_user_edit');
            }
        }

        return $this->render('admin/user/create_edit.html.twig', [
            'form' => $form,
        ]);
    }
}
