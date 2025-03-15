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
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            $this->logger->error($this->translator->trans(Message::DATA_NOT_FOUND), ['user' => $currentUser]);
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(UserEditType::class, $user, [
            'roles' => $currentUser->getRoles(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $userVarSportsAndNotAdmin = $user->isVarsportsMember() && (!$user->hasRole('ROLE_ADMIN') && !$user->hasRole('ROLE_MEMBER_VARSPORTS'));
                $userNotVarSportsAndAdmin = !$user->isVarsportsMember() && ($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_MEMBER_VARSPORTS'));
                if ($userVarSportsAndNotAdmin || $userNotVarSportsAndAdmin) {
                    $user->setIsVarsportsMember(false);
                    $user->setRoles(['ROLE_USER']);
                }

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
            'edit' => true,
        ]);
    }
}
