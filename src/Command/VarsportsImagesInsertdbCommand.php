<?php

namespace App\Command;

use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'varsports:images:insertdb',
    description: 'If images are already imported but not mapped to the clubs, associate them with the clubs in the database',
)]
class VarsportsImagesInsertdbCommand extends Command
{
    public function __construct(
        private ClubRepository $clubRepository,
        private EntityManagerInterface $entityManager,
        private string $imagesPath = 'public/images/uploads/club/',
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $clubs = $this->clubRepository->findAll();
        foreach ($clubs as $club) {
            if (!is_dir($this->imagesPath.$club->getId())) {
                continue;
            }

            if (file_exists($this->imagesPath.$club->getId().'/profile_photo.jpg')) {
                $club->setLogo('profile_photo.jpg');
            } elseif (file_exists($this->imagesPath.$club->getId().'/profile_photo.png')) {
                $club->setLogo('profile_photo.png');
            }

            if (file_exists($this->imagesPath.$club->getId().'/cover_photo.jpg')) {
                $club->setCoverImage('cover_photo.jpg');
            } elseif (file_exists($this->imagesPath.$club->getId().'/cover_photo.png')) {
                $club->setCoverImage('cover_photo.png');
            }

            $this->entityManager->persist($club);
        }

        $this->entityManager->flush();

        $io->success('Success');

        return Command::SUCCESS;
    }
}
