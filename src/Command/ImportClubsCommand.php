<?php

namespace App\Command;

use App\Constant\Message;
use App\Entity\Club;
use App\Entity\Discipline;
use App\Entity\User;
use App\Repository\DisciplineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsCommand(
    name: 'varsports:clubs:import',
    description: 'Import clubs from JSON file',
)]
class ImportClubsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DisciplineRepository $disciplineRepository,
        private SluggerInterface $slugger,
        private string $filePath = 'docker/imports/clubs_clean.json'
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

        $nbClubsCreated = 0;
        $nbAdminsCreated = 0;
        $rowsNotInserted = [];

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
            try {
                $disciplines = [];
                foreach ($dataClub['disciplines'] as $dataDiscipline) {
                    $disciplineInDB = $this->disciplineRepository->findOneBy(['label' => ucfirst(strtolower($dataDiscipline))]);

                    if (null !== $disciplineInDB) {
                        continue;
                    }

                    $discipline = (new Discipline())
                        ->setLabel(ucfirst(strtolower($dataDiscipline)));

                    $this->entityManager->persist($discipline);

                    $disciplines[] = $discipline;
                }

                $club = (new Club())
                    ->setName($dataClub['name'])
                    ->setEmail($dataClub['email'])
                    ->setSlug($this->slugger->slug($dataClub['name'])->lower())
                    ->setDescription($dataClub['description'])
                    ->setFacebook($dataClub['facebook'])
                    ->setInstagram($dataClub['instagram'])
                    ->setYoutube($dataClub['youtube'])
                    ->setTwitter($dataClub['twitter'])
                    ->setWebsite($dataClub['website'])
                    ->setAddress($dataClub['address'])
                    ->setPostalCode($dataClub['postal_code'])
                    ->setCity($dataClub['city'])
                    ->setPhone($dataClub['phone']);

                foreach ($disciplines as $discipline) {
                    $club->addDiscipline($discipline);
                }

                $this->entityManager->persist($club);

                $clubAdmin = (new User())
                    ->setEmail($dataClub['admin_email'])
                    ->setRoles(['ROLE_CLUB_ADMIN'])
                    ->setVerified(true)
                    ->setName($dataClub['lastname'])
                    ->setFirstName($dataClub['firstname'])
                    ->setClub($club)
                    ->setPassword(substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', intval(ceil(8 / strlen($x))))), 1, 8));

                $this->entityManager->persist($clubAdmin);

                ++$nbClubsCreated;
                ++$nbAdminsCreated;
            } catch (\Exception $e) {
                $rowsNotInserted[] = $dataClub['id'];
            }
        }

        $this->entityManager->flush();

        $io->note(sprintf('%d rows not inserted', count($rowsNotInserted)));
        $io->note(sprintf('Clubs not created: %s', implode(', ', $rowsNotInserted)));
        $io->note(sprintf('%d clubs created', $nbClubsCreated));
        $io->note(sprintf('%d admins created', $nbAdminsCreated));
        $io->success('Success');

        return Command::SUCCESS;
    }
}
