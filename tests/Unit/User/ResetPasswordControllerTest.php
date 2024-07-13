<?php

namespace App\Tests\Unit\User;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');

        $resetPasswordRepository = $this->entityManager->getRepository(ResetPasswordRequest::class);
        foreach ($resetPasswordRepository->findAll() as $resetPasswordRequest) {
            $this->entityManager->remove($resetPasswordRequest);
        }

        $this->userRepository = $this->entityManager->getRepository(User::class);
        foreach ($this->userRepository->findAll() as $user) {
            $this->entityManager->remove($user);
        }

        $this->entityManager->flush();

        // Create a User fixture
        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = $container->get('security.user_password_hasher');

        $user = (new User())
            ->setEmail('me@example.com')
            ->setRoles(['ROLE_ADMIN_CLUB'])
            ->setVerified(true);
        $user->setPassword($passwordHasher->hashPassword($user, '$$Aqw1Zsx2Edc1470'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function testResetPasswordController(): void
    {
        // Test Request reset password page
        $this->client->request('GET', '/reset-password');

        self::assertResponseIsSuccessful();

        // Submit the reset password form and test email message is queued / sent
        $this->client->submitForm('Envoyer', [
            'reset_password_request_form[email]' => 'me@example.com',
        ]);

        // Ensure the reset password email was sent
        // Use either assertQueuedEmailCount() || assertEmailCount() depending on your mailer setup
        // self::assertQueuedEmailCount(1);
        self::assertEmailCount(1);

        $messages = $this->getMailerMessages();
        self::assertEmailAddressContains($messages[0], 'from', 'no-reply@varsports.fr');
        self::assertEmailAddressContains($messages[0], 'to', 'me@example.com');

        self::assertResponseRedirects('/reset-password/check-email');

        // Test the link sent in the email is valid
        $email = $messages[0]->toString();
        preg_match('#(/reset-password/reset/[a-zA-Z0-9]+)#', $email, $resetLink);

        $this->client->request('GET', $resetLink[1]);

        self::assertResponseRedirects('/reset-password/reset');

        $this->client->followRedirect();

        // Test we can set a new password
        $this->client->submitForm('Envoyer', [
            'change_password_form[plainPassword][first]' => '%%Aqw1Zsx2Edc1470',
            'change_password_form[plainPassword][second]' => '%%Aqw1Zsx2Edc1470',
        ]);

        self::assertResponseRedirects('/login');

        $user = $this->userRepository->findOneBy(['email' => 'me@example.com']);

        self::assertInstanceOf(User::class, $user);

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($passwordHasher->isPasswordValid($user, '%%Aqw1Zsx2Edc1470'));
    }
}
