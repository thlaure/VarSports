<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Article;
use App\Entity\Club;
use App\Entity\User;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
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

class ArticleEditController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private ArticleRepository $articleRepository,
        private SluggerInterface $slugger,
        private FileChecker $fileChecker,
        private FileUploader $fileUploader,
        private string $targetDirectory
    ) {
    }

    #[Route('/admin/article/{id}/edit', name: 'app_admin_article_edit')]
    #[IsGranted('ROLE_MEMBER_CLUB', message: Message::GENERIC_GRANT_ERROR)]
    public function edit(int $id, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            $this->logger->error(Message::DATA_NOT_FOUND, ['user' => $user]);
            throw $this->createNotFoundException();
        }

        $article = $this->articleRepository->findOneBy(['id' => $id]);
        if (!$article instanceof Article) {
            $this->logger->error(Message::DATA_NOT_FOUND, ['article' => $article]);
            throw $this->createNotFoundException();
        }

        $isMemberAndHasClub = $user->hasRole('ROLE_MEMBER_CLUB') && (null !== $user->getClub() && null !== $article->getClub() && $user->getClub()->getId() === $article->getClub()->getId());
        if ($isMemberAndHasClub || null === $article->getClub() && !$user->hasRole('ROLE_ADMIN')) {
            $this->logger->error(Message::GENERIC_ACCESS_DENIED, ['user' => $user]);
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if (!$user->hasRole('ROLE_ADMIN') && !$user->getClub() instanceof Club) {
                    $this->logger->error(Message::DATA_MUST_BE_SET, ['user' => $user]);
                    $this->addFlash('warning', Message::CLUB_NOT_FOUND);
                    throw $this->createNotFoundException();
                } else {
                    $article->setClub($user->getClub());
                }

                $article->setAuthor($user);
                $article->setCreationDate(new \DateTimeImmutable());
                $article->setSlug($this->slugger->slug((string) $article->getTitle())->lower());

                $this->entityManager->persist($article);
                $this->entityManager->flush();

                $article->setSlug($this->slugger->slug((string) $article->getTitle())->lower().'-'.$article->getId());

                /** @var ?UploadedFile $image */
                $image = $form->get('image')->getData();
                if ($image && $this->fileChecker->checkImageIsValid($image)) {
                    $imageName = $this->fileUploader->upload($image, $this->targetDirectory.'/'.$article->getId());
                    $article->setImage($imageName);
                    $this->entityManager->persist($article);
                }

                $article->setLastUpdate(new \DateTime());

                $this->entityManager->flush();

                $this->addFlash('success', Message::GENERIC_SUCCESS);

                return $this->redirectToRoute('app_article_show', ['slug' => $article->getSlug()]);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->addFlash('error', Message::GENERIC_ERROR);
            }
        }

        return $this->render('admin/article/create_edit.html.twig', [
            'form' => $form,
            'title' => "Modifier l'article",
        ]);
    }
}
