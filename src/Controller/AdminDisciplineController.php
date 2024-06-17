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
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin/discipline', name: 'app_admin_discipline_')]
class AdminDisciplineController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/dashboard', name: 'dashboard')]
    #[IsGranted('ROLE_ADMIN_CLUB', message: Message::GENERIC_GRANT_ERROR)]
    public function create(Request $request, DisciplineRepository $disciplineRepository, ValidatorInterface $validator): Response
    {
        $discipline = new Discipline();
        $form = $this->createForm(DisciplineType::class, $discipline);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $errors = $validator->validate($form);
            if (count($errors) > 0) {
                /** @var ConstraintViolation $error */
                $error = $errors[0];
                $this->addFlash('error', $error->getMessage());

                return $this->redirectToRoute('app_admin_discipline_dashboard');
            }

            if ($form->isValid()) {
                try {
                    $this->entityManager->persist($discipline);
                    $this->entityManager->flush();

                    $this->addFlash('success', Message::GENERIC_SUCCESS);
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                    $this->addFlash('error', Message::GENERIC_ERROR);
                }
            }

            return $this->redirectToRoute('app_admin_discipline_dashboard');
        }

        return $this->render('admin/discipline/dashboard.html.twig', [
            'form' => $form,
            'disciplines' => $disciplineRepository->findAll(),
        ]);
    }

    #[Route('/{id}/delete', name: 'delete')]
    #[IsGranted('ROLE_ADMIN_CLUB', message: Message::GENERIC_GRANT_ERROR)]
    public function delete(int $id): Response
    {
        $discipline = $this->entityManager->getRepository(Discipline::class)->find($id);

        try {
            if (!$discipline instanceof Discipline) {
                throw new NotFoundResourceException(Message::DATA_NOT_FOUND, Response::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($discipline);
            $this->entityManager->flush();

            $this->addFlash('success', Message::GENERIC_SUCCESS);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->addFlash('error', Message::GENERIC_ERROR);
        }

        return $this->redirectToRoute('app_admin_discipline_dashboard');
    }

    #[Route('/{id}/edit', name: 'edit')]
    #[IsGranted('ROLE_ADMIN_CLUB', message: Message::GENERIC_GRANT_ERROR)]
    public function edit(int $id, DisciplineRepository $disciplineRepository, Request $request): Response
    {
        $disciplineToEdit = $disciplineRepository->find($id);
        $form = $this->createForm(DisciplineType::class, $disciplineToEdit);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->flush();

                $this->addFlash('success', Message::GENERIC_SUCCESS);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->addFlash('error', Message::GENERIC_ERROR);
            }

            return $this->redirectToRoute('app_admin_discipline_dashboard');
        }

        return $this->render('admin/discipline/dashboard.html.twig', [
            'form' => $form,
            'disciplines' => $disciplineRepository->findAll(),
        ]);
    }
}
