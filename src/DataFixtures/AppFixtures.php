<?php

namespace App\DataFixtures;

use App\Entity\Club;
use App\Entity\Discipline;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; ++$i) {
            $discipline = new Discipline();
            $discipline->setLabel('Discipline '.$i);
            $manager->persist($discipline);
        }
        

        for ($i = 0; $i < 3; ++$i) {
            $club = new Club();
            $club->setName('Club '.$i)
                ->setAddress('Adresse '.$i)
                ->setPostalCode('12345')
                ->setCity('Ville '.$i)
                ->setPhone('0123456789')
                ->setWebsite('https://www.club'.$i.'.com');
            $manager->persist($club);
        }

        $manager->flush();
    }
}
