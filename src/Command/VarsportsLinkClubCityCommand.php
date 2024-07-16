<?php

namespace App\Command;

use App\Entity\Club;
use App\Repository\CityRepository;
use App\Repository\ClubRepository;
use App\Repository\PostalCodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'varsports:link:club-city',
    description: 'ALink the City entity to the Club entity',
)]
class VarsportsLinkClubCityCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ClubRepository $clubRepository,
        private CityRepository $cityRepository,
        private PostalCodeRepository $postalCodeRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Varsports link club city');

        $clubs = $this->clubRepository->findAll();
        foreach ($clubs as $club) {
            if (null === $club->getCity() || null === $club->getPostalCode()) {
                continue;
            }

            $city = $this->cityRepository->findOneBy(['name' => $club->getCity()]);
            $postalCode = $this->postalCodeRepository->findOneBy(['code' => $club->getPostalCode()]);

            if (null === $city || null === $postalCode) {
                $io->error('City and postal code not found for club '.$club->getId());
                continue;
            }

            if ($club instanceof Club) {
                $club->setCityfk($city);
                $club->setPostalCodefk($postalCode);
                $this->entityManager->flush();
            }
        }

        $io->success('Success');

        return Command::SUCCESS;
    }
}
