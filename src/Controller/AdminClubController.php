<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Club;
use App\Form\ClubType;
use App\Repository\ClubRepository;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin/club', name: 'app_admin_club_')]
class AdminClubController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private string $targetDirectory
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
                    /** @var ?UploadedFile $image */
                    $logo = $form->get('logo')->getData();
                    if ($logo && $fileChecker->checkImageIsValid($logo)) {
                        $logoName = $fileUploader->upload($logo, $this->targetDirectory);
                        $club->setLogo($logoName);
                    }

                    $club->setSlug($slugger->slug($club->getName())->lower());

                    $this->entityManager->persist($club);
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
        $club = $clubRepository->findOneBy(['id' => $id]);

        try {
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
    public function edit(int $id, ClubRepository $clubRepository, Request $request, FileChecker $fileChecker, FileUploader $fileUploader): Response
    {
        $club = $clubRepository->findOneBy(['id' => $id]);
        $form = $this->createForm(ClubType::class, $club);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var ?UploadedFile $image */
                $logo = $form->get('logo')->getData();
                if ($logo && $fileChecker->checkImageIsValid($logo)) {
                    $logoName = $fileUploader->upload($logo, $this->targetDirectory);
                    $club->setLogo($logoName);
                }

                $this->entityManager->flush();

                $this->addFlash('success', Message::GENERIC_SUCCESS);

                return $this->redirectToRoute('app_club_show', ['id' => $club->getId()]);
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
