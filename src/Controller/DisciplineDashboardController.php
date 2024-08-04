<?php

namespace App\Controller;

use App\Constant\Message;
use App\Entity\Discipline;
use App\Form\DisciplineType;
use App\Repository\DisciplineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/discipline', name: 'app_admin_discipline_')]
class DisciplineDashboardController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private DisciplineRepository $disciplineRepository
    ) {
    }

    #[Route('/dashboard', name: 'dashboard')]
    #[IsGranted('ROLE_ADMIN', message: Message::GENERIC_GRANT_ERROR)]
    public function create(Request $request): Response
    {
        $discipline = new Discipline();
        $form = $this->createForm(DisciplineType::class, $discipline);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->persist($discipline);
                $this->entityManager->flush();

                $this->addFlash('success', Message::GENERIC_SUCCESS);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->addFlash('error', Message::GENERIC_ERROR);
            }
        }

        return $this->render('admin/discipline/dashboard.html.twig', [
            'form' => $form,
            'disciplines' => $this->disciplineRepository->findBy([], ['label' => 'ASC']),
        ]);
    }
}
