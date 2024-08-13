<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Article;
use App\Entity\Club;
use App\Entity\User;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ArticleCreateController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/admin/article/create', name: 'app_article_create')]
    #[IsGranted('ROLE_MEMBER_CLUB', message: Message::GENERIC_GRANT_ERROR)]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            $this->logger->error(Message::DATA_NOT_FOUND, ['user' => $user]);
            throw $this->createNotFoundException();
        }

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if (!$user->getClub() instanceof Club) {
                    $this->logger->error(Message::DATA_MUST_BE_SET, ['user' => $user]);
                    $this->addFlash('warning', Message::CLUB_NOT_FOUND);
                    throw $this->createNotFoundException();
                }

                $article->setClub($user->getClub());
                $article->setCreationDate(new \DateTimeImmutable());

                $this->entityManager->persist($article);
                $this->entityManager->flush();

                $this->addFlash('success', Message::GENERIC_SUCCESS);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->addFlash('error', Message::GENERIC_ERROR);
            }
        }

        return $this->render('admin/article/create.html.twig', [
            'form' => $form,
        ]);
    }
}
