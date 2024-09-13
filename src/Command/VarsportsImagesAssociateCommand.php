<?php

namespace App\Command;

use App\Constant\Message;
use App\Entity\Club;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'varsports:images:associate',
    description: 'Associate the imported images from the initial server with the imported clubs',
)]
class VarsportsImagesAssociateCommand extends Command
{
    public function __construct(
        private ClubRepository $clubRepository,
        private EntityManagerInterface $entityManager,
        private Filesystem $filesystem,
        private TranslatorInterface $translator,
        private string $filePath = 'docker/imports/clubs_clean.json',
        private string $oldImagesPath = 'public/images/uploads/club/old/',
        private string $newImagesPath = 'public/images/uploads/club/',
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '256M');

        $io = new SymfonyStyle($input, $output);

        if (!file_exists($this->filePath)) {
            $io->error($this->translator->trans(Message::FILE_NOT_FOUND));

            return Command::FAILURE;
        }

        $fileContent = file_get_contents($this->filePath);
        if (false === $fileContent) {
            $io->error($this->translator->trans(Message::FILE_NOT_READABLE));

            return Command::FAILURE;
        }

        $data = json_decode($fileContent, true);
        if (null === $data || !is_array($data)) {
            $io->error($this->translator->trans(Message::GENERIC_ERROR));

            return Command::FAILURE;
        }

        $profilePhotoName = 'profile_photo.jpg';
        $coverPhotoName = 'cover_photo.jpg';
        foreach ($data as $dataClub) {
            if ($this->filesystem->exists($this->oldImagesPath.$dataClub['id'])) {
                $club = $this->clubRepository->findOneBy(['email' => $dataClub['email']]);
                if (!$club instanceof Club) {
                    $io->error($this->translator->trans(Message::GENERIC_ERROR));
                    continue;
                }

                $this->filesystem->mkdir($this->newImagesPath.$club->getId(), 0700);
                if ($this->filesystem->exists($this->oldImagesPath.$dataClub['id'].'/'.$profilePhotoName)) {
                    $this->filesystem->copy($this->oldImagesPath.$dataClub['id'].'/'.$profilePhotoName, $this->newImagesPath.$club->getId().'/'.$profilePhotoName);
                    $club->setLogo(''.$profilePhotoName);
                }

                if ($this->filesystem->exists($this->oldImagesPath.$dataClub['id'].'/'.$coverPhotoName)) {
                    $this->filesystem->copy($this->oldImagesPath.$dataClub['id'].'/'.$coverPhotoName, $this->newImagesPath.$club->getId().'/'.$coverPhotoName);
                    $club->setCoverImage(''.$coverPhotoName);
                }

                $this->entityManager->persist($club);
            }
        }

        $this->entityManager->flush();

        $io->success('Success');

        return Command::SUCCESS;
    }
}
