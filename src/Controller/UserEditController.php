<?php

namespace App\Controller;

use App\Constant\Message;
use App\Form\UserEditType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserEditController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {
    }

    #[Route('/admin/user/{id}/edit', name: 'app_user_edit')]
    #[IsGranted('ROLE_ADMIN_CLUB', message: Message::GENERIC_GRANT_ERROR)]
    public function edit(int $id, Request $request): Response
    {
        $user = $this->userRepository->findOneBy(['id' => $id]);
        if (null === $user) {
            $this->logger->error(Message::DATA_NOT_FOUND);
            throw $this->createNotFoundException(Message::DATA_NOT_FOUND);
        }

        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->flush();
                $this->addFlash('success', Message::GENERIC_SUCCESS);

                return $this->redirectToRoute('app_user_list');
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->addFlash('error', Message::GENERIC_ERROR);

                return $this->redirectToRoute('app_user_edit');
            }
        }

        return $this->render('admin/user/create_edit.html.twig', [
            'form' => $form,
        ]);
    }
}
