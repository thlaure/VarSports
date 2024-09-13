<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Article;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArticleDeleteController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private TranslatorInterface $translator,
    ) {
    }

    #[Route('/admin/article/{id}/delete', name: 'app_admin_article_delete')]
    #[IsGranted('ROLE_ADMIN_CLUB')]
    public function delete(Article $article): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            $this->logger->error($this->translator->trans(Message::DATA_NOT_FOUND), ['user' => $user]);
            throw new NotFoundResourceException($this->translator->trans(Message::DATA_NOT_FOUND), Response::HTTP_NOT_FOUND);
        }

        if (
            ($user->hasRole('ROLE_ADMIN_CLUB') && $user->getClub() && $article->getClub() && $user->getClub()->getId() !== $article->getClub()->getId())
            || (!$user->hasRole('ROLE_ADMIN_CLUB') && !$user->hasRole('ROLE_ADMIN'))
        ) {
            $this->logger->error($this->translator->trans(Message::GENERIC_ACCESS_DENIED), ['user' => $user]);
            throw new AccessDeniedHttpException();
        }

        try {
            $this->entityManager->remove($article);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans(Message::GENERIC_SUCCESS));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->addFlash('error', $this->translator->trans(Message::GENERIC_ERROR).' '.$e->getMessage());
        }

        return $this->redirectToRoute('app_article_list');
    }
}
