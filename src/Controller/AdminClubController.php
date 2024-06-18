<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Club;
use App\Entity\User;
use App\Form\ClubType;
use App\Repository\ClubRepository;
use App\Service\FileChecker;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin/club', name: 'app_admin_club_')]
class AdminClubController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private string $targetDirectory,
        private Security $security
    ) {
    }

    #[Route('/create', name: 'create')]
    #[IsGranted('ROLE_ADMIN_CLUB', message: Message::GENERIC_GRANT_ERROR)]
    public function create(Request $request, ValidatorInterface $validator, FileChecker $fileChecker, FileUploader $fileUploader, SluggerInterface $slugger): Response
    {
        $club = new Club();
        $form = $this->createForm(ClubType::class, $club);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $errors = $validator->validate($form);
            if (count($errors) > 0) {
                /** @var ConstraintViolation $error */
                $error = $errors[0];
                $this->addFlash('error', $error->getMessage());

                return $this->redirectToRoute('app_admin_club_create');
            }

            if ($form->isValid()) {
                try {
                    /** @var ?UploadedFile $logo */
                    $logo = $form->get('logo')->getData();
                    if ($logo && $fileChecker->checkImageIsValid($logo)) {
                        $logoName = $fileUploader->upload($logo, $this->targetDirectory);
                        $club->setLogo($logoName);
                    }

                    if (!is_string($club->getName()) || empty($club->getName())) {
                        throw new \InvalidArgumentException(Message::DATA_MUST_BE_SET, Response::HTTP_BAD_REQUEST);
                    }
                    $club->setSlug($slugger->slug($club->getName())->lower());

                    $this->entityManager->persist($club);

                    $user = $this->security->getUser();
                    if ($user instanceof User) {
                        $user->setClub($club);
                        $this->entityManager->persist($user);
                    }

                    $this->entityManager->flush();

                    $this->addFlash('success', Message::GENERIC_SUCCESS);

                    return $this->redirectToRoute('app_club_list');
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                    $this->addFlash('error', Message::GENERIC_ERROR);
                }
            }
        }

        return $this->render('admin/club/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'delete')]
    #[IsGranted('ROLE_ADMIN_CLUB', message: Message::GENERIC_GRANT_ERROR)]
    public function delete(int $id, ClubRepository $clubRepository): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new NotFoundResourceException(Message::DATA_NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        $club = $clubRepository->findOneBy(['id' => $id]);
        if (null === $user->getClub() || $user->getClub()->getId() !== $club->getId()) {
            throw new AccessDeniedHttpException(Message::GENERIC_ACCESS_DENIED);
        }

        try {
            if (!$club instanceof Club) {
                throw new NotFoundResourceException(Message::DATA_NOT_FOUND, Response::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($club);
            $this->entityManager->flush();

            $this->addFlash('success', Message::GENERIC_SUCCESS);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->addFlash('error', Message::GENERIC_ERROR);
        }

        return $this->redirectToRoute('app_club_list');
    }

    #[Route('/{id}/edit', name: 'edit')]
    #[IsGranted('ROLE_ADMIN_CLUB', message: Message::GENERIC_GRANT_ERROR)]
    public function edit(int $id, ClubRepository $clubRepository, Request $request, FileChecker $fileChecker, FileUploader $fileUploader, SluggerInterface $slugger): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new NotFoundResourceException(Message::DATA_NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        $club = $clubRepository->findOneBy(['id' => $id]);
        if (null === $user->getClub() || $user->getClub()->getId() !== $club->getId()) {
            throw new AccessDeniedHttpException(Message::GENERIC_ACCESS_DENIED);
        }

        $form = $this->createForm(ClubType::class, $club);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if (!$club instanceof Club) {
                    throw new NotFoundResourceException(Message::DATA_NOT_FOUND, Response::HTTP_NOT_FOUND);
                }

                /** @var ?UploadedFile $logo */
                $logo = $form->get('logo')->getData();
                if ($logo && $fileChecker->checkImageIsValid($logo)) {
                    $logoName = $fileUploader->upload($logo, $this->targetDirectory);
                    $club->setLogo($logoName);
                }

                if (!is_string($club->getName()) || empty($club->getName())) {
                    throw new \InvalidArgumentException(Message::DATA_MUST_BE_SET, Response::HTTP_BAD_REQUEST);
                }
                $club->setSlug($slugger->slug($club->getName())->lower());

                $this->entityManager->flush();

                $this->addFlash('success', Message::GENERIC_SUCCESS);

                return $this->redirectToRoute('app_club_show', ['slug' => $club->getSlug()]);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->addFlash('error', Message::GENERIC_ERROR);
            }
        }

        return $this->render('admin/club/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
