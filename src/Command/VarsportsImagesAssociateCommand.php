<?php

namespace App\Command;

use App\Constant\Message;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'varsports:images:associate',
    description: 'Associate the imported images from the initial server with the imported clubs',
)]
class VarsportsImagesAssociateCommand extends Command
{
    public function __construct(
        private ClubRepository $clubRepository,
        private EntityManagerInterface $entityManager,
        private string $filePath = 'docker/imports/clubs_clean.json',
        private string $oldImagesPath = 'public/images/uploads/club/old/',
        private string $newImagesPath = 'public/images/uploads/club/'
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '256M');

        $io = new SymfonyStyle($input, $output);

        if (!file_exists($this->filePath)) {
            $io->error(Message::FILE_NOT_FOUND);

            return Command::FAILURE;
        }

        $fileContent = file_get_contents($this->filePath);
        if (false === $fileContent) {
            $io->error(Message::FILE_NOT_READABLE);

            return Command::FAILURE;
        }

        $data = json_decode($fileContent, true);
        if (null === $data || !is_array($data)) {
            $io->error(Message::GENERIC_ERROR);

            return Command::FAILURE;
        }

        foreach ($data as $dataClub) {
            if (is_dir($this->oldImagesPath.$dataClub['id'])) {
                $club = $this->clubRepository->findOneBy(['email' => $dataClub['email']]);
                mkdir($this->newImagesPath.$club->getId(), 0755, true);
                if (file_exists($this->oldImagesPath.$dataClub['id'].'/profile_photo.jpg')) {
                    copy($this->oldImagesPath.$dataClub['id'].'/profile_photo.jpg', $this->newImagesPath.$club->getId().'/profile_photo.jpg');
                    $club->setLogo('profile_photo.jpg');
                } elseif (file_exists($this->oldImagesPath.$dataClub['id'].'/profile_photo.png')) {
                    copy($this->oldImagesPath.$dataClub['id'].'/profile_photo.png', $this->newImagesPath.$club->getId().'/profile_photo.png');
                    $club->setLogo('profile_photo.png');
                }

                if (file_exists($this->oldImagesPath.$dataClub['id'].'/cover_photo.jpg')) {
                    copy($this->oldImagesPath.$dataClub['id'].'/cover_photo.jpg', $this->newImagesPath.$club->getId().'/cover_photo.jpg');
                    $club->setCoverImage('cover_photo.jpg');
                } elseif (file_exists($this->oldImagesPath.$dataClub['id'].'/cover_photo.png')) {
                    copy($this->oldImagesPath.$dataClub['id'].'/cover_photo.png', $this->newImagesPath.$club->getId().'/cover_photo.png');
                    $club->setCoverImage('cover_photo.png');
                }

                $this->entityManager->persist($club);
            }
        }

        $this->entityManager->flush();

        $io->success('Success');

        return Command::SUCCESS;
    }
}
