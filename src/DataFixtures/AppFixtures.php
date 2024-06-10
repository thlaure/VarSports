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
                ->setEmail('club'.$i.'@mail.com')
                ->setWebsite('https://www.club'.$i.'.com')
                ->setLogo('white.png')
                ->setDescription('Culpa eiusmod ullamco occaecat dolore veniam eu officia tempor eiusmod ut et reprehenderit veniam sint. Et ea exercitation nulla fugiat eiusmod elit labore voluptate aliquip nulla. Excepteur consectetur eu enim occaecat. Elit pariatur nulla excepteur anim do. Enim cillum exercitation proident aute aliqua do est ex labore nisi ea et. Eu ut minim cillum veniam fugiat aute quis consectetur culpa. Mollit do officia cupidatat voluptate laborum deserunt fugiat Lorem veniam.');
            $manager->persist($club);
        }

        $manager->flush();
    }
}
