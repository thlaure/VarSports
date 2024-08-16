<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Article;
use App\Entity\Club;
use App\Entity\User;
use App\Form\ArticleType;
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

class ArticleCreateController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger,
        private FileChecker $fileChecker,
        private FileUploader $fileUploader,
        private string $targetDirectory
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

                $article->setSlug($this->slugger->slug((string) $article->getTitle())->lower());

                $this->entityManager->persist($article);
                $this->entityManager->flush();

                $article->setSlug($this->slugger->slug((string) $article->getTitle())->lower().'-'.$article->getId());

                /** @var ?UploadedFile $image */
                $image = $form->get('image')->getData();
                if ($image && $this->fileChecker->checkImageIsValid($image) && null !== $article->getClub()) {
                    $imageName = $this->fileUploader->upload($image, $this->targetDirectory.'/'.$article->getClub()->getId().'/articles');
                    $article->setImage($imageName);
                    $this->entityManager->persist($article);
                }

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
