<?php

namespace App\Command;

use App\Entity\City;
use App\Entity\Club;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'varsports:separate:city',
    description: 'Separate the city from the club entity',
)]
class VarsportsSeparateCityCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Varsports - Separate city');

        $clubs = $this->entityManager->getRepository(Club::class)->findAll();
        foreach ($clubs as $club) {
            if (!$club->getCityName() || !$club->getPostalCodeCode()) {
                continue;
            }

            $city = $this->entityManager->getRepository(City::class)->findOneBy([
                'name' => $club->getCityName(),
                'postalCode' => $club->getPostalCodeCode(),
            ]);

            if ($city) {
                $club->setCity($city);
                continue;
            }

            $city = new City();
            $city->setName($club->getCityName());
            $city->setPostalCode($club->getPostalCodeCode());
            $club->setCity($city);

            $this->entityManager->persist($city);

            $this->entityManager->flush();

            $io->info('City created: '.$club->getCityName());
        }

        $io->success('Success');

        return Command::SUCCESS;
    }
}
