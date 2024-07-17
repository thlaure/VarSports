<?php

namespace App\Command;

use App\Entity\City;
use App\Entity\Department;
use App\Entity\PostalCode;
use App\Repository\CityRepository;
use App\Repository\ClubRepository;
use App\Repository\DepartmentRepository;
use App\Repository\PostalCodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'varsports:club-city:separate',
    description: 'Add a short description for your command',
)]
class VarsportsClubCitySeparateCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ClubRepository $clubRepository,
        private CityRepository $cityRepository,
        private PostalCodeRepository $postalCodeRepository,
        private DepartmentRepository $departmentRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Varsports club city separate');

        $defaultDepartment = $this->departmentRepository->findOneBy(['code' => '83']);
        if (!$defaultDepartment instanceof Department) {
            $io->error('Department not found');

            return Command::FAILURE;
        }

        $nbCitiesInserted = 0;
        $nbPostalCodesInserted = 0;

        $clubs = $this->clubRepository->findAll();
        foreach ($clubs as $club) {
            if (null === $club->getCityName() || null === $club->getPostalCodeCode()) {
                continue;
            }

            $postalCode = $this->postalCodeRepository->findOneBy(['code' => $club->getPostalCodeCode()]);
            $io->info('Postal code '.$club->getPostalCodeCode().' found: '.($postalCode instanceof PostalCode ? 'yes' : 'no'));
            if (!$postalCode instanceof PostalCode) {
                $postalCode = (new PostalCode())
                    ->setCode($club->getPostalCodeCode());
                $io->comment('Postal code '.$club->getPostalCodeCode());

                try {
                    $this->entityManager->persist($postalCode);
                    $this->entityManager->flush();
                    $io->comment('Inserted postal code '.$postalCode->getCode());
                    ++$nbPostalCodesInserted;
                } catch (\Exception $exception) {
                    $io->error($exception->getMessage());

                    return Command::FAILURE;
                }
            }

            $city = $this->cityRepository->findOneBy(['name' => $club->getCityName()]);
            $io->info('City '.$club->getCityName().' found: '.($city instanceof City ? 'yes' : 'no'));
            if (!$city instanceof City) {
                $city = (new City())
                    ->setDepartment($defaultDepartment)
                    ->setName($club->getCityName());
                $io->comment('City '.$club->getCityName());

                try {
                    $this->entityManager->persist($city);
                    $this->entityManager->flush();
                    $io->comment('Inserted city '.$city->getName());
                    ++$nbCitiesInserted;
                } catch (\Exception $exception) {
                    $io->error($exception->getMessage());

                    return Command::FAILURE;
                }
            }

            if (!$city->getPostalCodes()->contains($postalCode)) {
                $city->addPostalCode($postalCode);
            }

            $this->entityManager->flush();
        }

        $io->info('Inserted '.$nbCitiesInserted.' cities');
        $io->info('Inserted '.$nbPostalCodesInserted.' postal codes');

        $io->success('Success');

        return Command::SUCCESS;
    }
}
