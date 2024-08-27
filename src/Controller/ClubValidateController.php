<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Club;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ClubValidateController extends AbstractController
{
    public function __construct(
        private ClubRepository $clubRepository,
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/club/{id}/validate', name: 'app_admin_club_validate')]
    #[IsGranted('ROLE_ADMIN', message: Message::GENERIC_GRANT_ERROR)]
    public function validate(int $id): Response
    {
        $club = $this->clubRepository->findOneBy(['id' => $id]);
        if (!$club instanceof Club) {
            $this->logger->error(Message::DATA_NOT_FOUND, ['club' => $club]);
            throw $this->createNotFoundException();
        }

        $club->setValidated(!$club->isValidated());
        $this->entityManager->flush();

        return $this->redirectToRoute('app_club_show', ['slug' => $club->getSlug()]);
    }
}
