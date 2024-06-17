<?php

namespace App\DataFixtures;

use App\Entity\Club;
use App\Entity\Discipline;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private SluggerInterface $slugger
    )
    {
        
    }
    public function load(ObjectManager $manager): void
    {
        $discipline1 = (new Discipline())->setLabel('Volleyball');
        $discipline2 = (new Discipline())->setLabel('Football');
        $discipline3 = (new Discipline())->setLabel('Handball');
        $manager->persist($discipline1);
        $manager->persist($discipline2);
        $manager->persist($discipline3);

        for ($i = 0; $i < 3; ++$i) {
            $club = new Club();
            $club->setName('Club '.$i)
                ->setAddress('Adresse '.$i)
                ->setPostalCode('12345')
                ->setCity('Ville')
                ->setPhone('0123456789')
                ->setEmail('club'.$i.'@mail.com')
                ->setWebsite('https://www.club'.$i.'.com')
                ->setLogo('white.png')
                ->setSlug($this->slugger->slug($club->getName())->lower())
                ->setDescription('Culpa eiusmod ullamco occaecat dolore veniam eu officia tempor eiusmod ut et reprehenderit veniam sint. Et ea exercitation nulla fugiat eiusmod elit labore voluptate aliquip nulla. Excepteur consectetur eu enim occaecat. Elit pariatur nulla excepteur anim do. Enim cillum exercitation proident aute aliqua do est ex labore nisi ea et. Eu ut minim cillum veniam fugiat aute quis consectetur culpa. Mollit do officia cupidatat voluptate laborum deserunt fugiat Lorem veniam.')
                ->addDiscipline($discipline1)
                ->addDiscipline($discipline2)
                ->addDiscipline($discipline3);
            $manager->persist($club);

            $admin = new User();
            $admin->setEmail('admin'.$i.'@localhost.com')
                ->setName('Admin '.$i)
                ->setFirstName('Admin')
                ->setRoles(['ROLE_ADMIN_CLUB'])
                ->setVerified(true)
                ->setPassword('$2y$10$LfClmCQbaH0MfcZLNMJkOeEC/6/WP88KFQnLrnF6x.o1NxwjGdGGG') // password
                ->setClub($club);
            $manager->persist($admin);

            $member = new User();
            $member->setEmail('member'.$i.'@localhost.com')
                ->setName('Member '.$i)
                ->setFirstName('Member')
                ->setRoles(['ROLE_MEMBER_CLUB'])
                ->setVerified(true)
                ->setPassword('$2y$10$LfClmCQbaH0MfcZLNMJkOeEC/6/WP88KFQnLrnF6x.o1NxwjGdGGG')
                ->setClub($club);
            $manager->persist($member);
        }

        $manager->flush();
    }
}
