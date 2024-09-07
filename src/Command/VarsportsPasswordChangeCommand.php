<?php

namespace App\Command;

use App\Constant\Message;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'varsports:password:change',
    description: 'Encode and update the user passwords',
)]
class VarsportsPasswordChangeCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $userPasswordHasher,
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
        private string $filePath = 'docker/imports/user_passwords.csv'
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Varsports - Update user passwords');

        if (!file_exists($this->filePath)) {
            touch($this->filePath);
        }

        $file = fopen($this->filePath, 'w+');
        if (!$file) {
            $io->error($this->translator->trans(Message::FILE_NOT_WRITABLE));

            return Command::FAILURE;
        }

        $users = $this->userRepository->findAll();
        foreach ($users as $user) {
            $plainPassword = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@^()[]{}°&éèà-_!?,;./:=+%ù$*§', intval(ceil(8 / strlen($x))))), 1, 12);

            fputcsv($file, [$user->getEmail(), $plainPassword], '|');

            $user->setPassword($this->userPasswordHasher->hashPassword(
                $user, $plainPassword
            ));
        }

        $this->entityManager->flush();

        $io->success('Success');

        return Command::SUCCESS;
    }
}
