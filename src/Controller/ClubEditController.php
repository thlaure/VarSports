<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\City;
use App\Entity\Club;
use App\Entity\User;
use App\Form\ClubType;
use App\Repository\ClubRepository;
use App\Service\FileChecker;
use App\Service\FileRemover;
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
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin/club', name: 'app_admin_club_')]
class ClubEditController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private ClubRepository $clubRepository,
        private string $targetDirectory,
        private FileChecker $fileChecker,
        private FileUploader $fileUploader,
        private FileRemover $fileRemover,
        private SluggerInterface $slugger,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('/{id}/edit', name: 'edit')]
    #[IsGranted('ROLE_ADMIN_CLUB', message: Message::GENERIC_GRANT_ERROR)]
    public function edit(int $id, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            $this->logger->error(Message::DATA_NOT_FOUND, ['user' => $user]);
            throw $this->createNotFoundException('User not found');
        }

        $club = $this->clubRepository->findOneBy(['id' => $id]);
        if (!$club instanceof Club) {
            $this->logger->error(Message::DATA_NOT_FOUND, ['club' => $club]);
            throw $this->createNotFoundException('Club not found');
        }

        if ($user->hasRole('ROLE_ADMIN_CLUB') && (null === $user->getClub() || $user->getClub()->getId() !== $club->getId())) {
            $this->logger->error(Message::GENERIC_ACCESS_DENIED, ['user' => $user]);
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ClubType::class, $club, [
            'roles' => $user->getRoles(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $errors = $this->validator->validate($form);
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
                    if ($logo && $this->fileChecker->checkImageIsValid($logo)) {
                        $logoName = $this->fileUploader->upload($logo, $this->targetDirectory.'/'.$club->getId());
                        if ($club->getLogo()) {
                            $this->fileRemover->remove($club->getLogo(), $this->targetDirectory.'/'.$club->getId());
                        }
                        $club->setLogo($logoName);
                    }

                    /** @var ?UploadedFile $cover */
                    $cover = $form->get('coverImage')->getData();
                    if ($cover && $this->fileChecker->checkImageIsValid($cover)) {
                        $coverName = $this->fileUploader->upload($cover, $this->targetDirectory.'/'.$club->getId());
                        if ($club->getCoverImage()) {
                            $this->fileRemover->remove($club->getCoverImage(), $this->targetDirectory.'/'.$club->getId());
                        }
                        $club->setCoverImage($coverName);
                    }

                    if (!is_string($club->getName()) || empty($club->getName())) {
                        $this->logger->error(Message::DATA_MUST_BE_SET, ['club' => $club]);
                        throw new \InvalidArgumentException(Message::DATA_MUST_BE_SET, Response::HTTP_BAD_REQUEST);
                    }

                    $newSlug = $this->slugger->slug((string) $club->getName())->lower();
                    if ($club->getSlug() !== $newSlug) {
                        $club->setSlug($newSlug.'-'.$club->getId());
                    }

                    if (null === $club->getCity()) {
                        $this->logger->error(Message::DATA_MUST_BE_SET, ['club' => $club]);
                        throw new \InvalidArgumentException(Message::DATA_MUST_BE_SET, Response::HTTP_BAD_REQUEST);
                    }

                    $existingCity = $this->entityManager->getRepository(City::class)->findOneBy([
                        'name' => $club->getCity()->getName(),
                        'postalCode' => $club->getCity()->getPostalCode(),
                    ]);

                    if (null !== $existingCity) {
                        $club->setCity($existingCity);
                    } else {
                        $this->entityManager->persist($club->getCity());
                    }

                    $club->setLastUpdate(new \DateTime());

                    $this->entityManager->flush();

                    $this->addFlash('success', Message::GENERIC_SUCCESS);

                    return $this->redirectToRoute('app_club_show', ['slug' => $club->getSlug()]);
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                    $this->addFlash('error', Message::GENERIC_ERROR);
                }
            }
        }

        return $this->render('admin/club/create_edit.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier le club',
        ]);
    }
}
