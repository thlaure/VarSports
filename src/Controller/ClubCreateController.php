<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\City;
use App\Entity\Club;
use App\Entity\User;
use App\Form\ClubType;
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

#[Route('/admin/club', name: 'app_admin_club_')]
class ClubCreateController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private string $targetDirectory,
        private FileChecker $fileChecker,
        private FileUploader $fileUploader,
        private SluggerInterface $slugger
    ) {
    }

    #[Route('/create', name: 'create')]
    #[IsGranted('ROLE_ADMIN_CLUB', message: Message::GENERIC_GRANT_ERROR)]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            $this->logger->error(Message::DATA_NOT_FOUND, ['user' => $user]);
            throw $this->createNotFoundException();
        }

        if (!$user->hasRole('ROLE_ADMIN') && $user->getClub() instanceof Club) {
            $this->addFlash('warning', Message::CLUB_ALREADY_EXISTS_FOR_THIS_ACCOUNT);

            return $this->redirectToRoute('app_admin_club_edit', ['id' => $user->getClub()->getId()]);
        }

        $club = new Club();
        $form = $this->createForm(ClubType::class, $club, [
            'roles' => $user->getRoles(),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if (!is_string($club->getName())) {
                    $this->logger->error(Message::DATA_MUST_BE_SET, ['club' => $club]);
                    throw new \InvalidArgumentException(Message::DATA_MUST_BE_SET, Response::HTTP_BAD_REQUEST);
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

                $club->setSlug($this->slugger->slug((string) $club->getName())->lower());

                $this->entityManager->persist($club);
                $this->entityManager->flush();

                $club->setSlug($this->slugger->slug((string) $club->getName())->lower().'-'.$club->getId());

                /** @var ?UploadedFile $logo */
                $logo = $form->get('logo')->getData();
                if ($logo && $this->fileChecker->checkImageIsValid($logo)) {
                    $logoName = $this->fileUploader->upload($logo, $this->targetDirectory.'/'.$club->getId());
                    $club->setLogo($logoName);
                    $this->entityManager->persist($club);
                }

                /** @var ?UploadedFile $cover */
                $cover = $form->get('coverImage')->getData();
                if ($cover && $this->fileChecker->checkImageIsValid($cover)) {
                    $coverName = $this->fileUploader->upload($cover, $this->targetDirectory.'/'.$club->getId());
                    $club->setCoverImage($coverName);
                    $this->entityManager->persist($club);
                }

                if ($user->hasRole('ROLE_ADMIN')) {
                    /** @var string|null $emailAdminClub */
                    $emailAdminClub = $form->get('admin_email')->getData();
                    if ($emailAdminClub) {
                        $adminClub = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $emailAdminClub]);
                        if ($adminClub instanceof User) {
                            $adminClub->setClub($club);
                        } else {
                            throw $this->createNotFoundException();
                        }
                    }
                } else {
                    /* @var User $user */
                    $user->setClub($club);
                    $this->entityManager->persist($user);
                }

                $this->entityManager->flush();

                $this->addFlash('success', Message::GENERIC_SUCCESS);

                return $this->redirectToRoute('app_club_list');
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('admin/club/create_edit.html.twig', [
            'form' => $form->createView(),
            'title' => 'Créer un club',
        ]);
    }
}
