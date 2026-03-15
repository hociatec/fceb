<?php

namespace App\Tests\Web;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class FormFlowsTest extends DatabaseWebTestCase
{
    public function testRegistrationCreatesUserAndRedirectsToLogin(): void
    {
        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->filter('form')->form([
            'registration_form[fullName]' => 'Test User',
            'registration_form[email]' => 'test+'.bin2hex(random_bytes(4)).'@example.com',
            'registration_form[plainPassword][first]' => 'MotDePasse123!',
            'registration_form[plainPassword][second]' => 'MotDePasse123!',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/login');
        self::assertSame(1, static::getContainer()->get(UserRepository::class)->count([]));
    }

    public function testContactFormRedirectsBackToContactOnSuccess(): void
    {
        $crawler = $this->client->request('GET', '/contact');
        $form = $crawler->filter('form')->form([
            'contact_form[name]' => 'Test Contact',
            'contact_form[email]' => 'contact@example.com',
            'contact_form[subject]' => 'Demande de renseignement',
            'contact_form[message]' => 'Bonjour, je souhaite obtenir plus d’informations sur le club.',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/contact');
    }

    public function testForgotPasswordSetsResetTokenForExistingUser(): void
    {
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user = (new User())
            ->setFullName('Reset User')
            ->setEmail('reset@example.com')
            ->setRoles(['ROLE_USER']);
        $user->setPassword($passwordHasher->hashPassword($user, 'Secret123!'));

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $crawler = $this->client->request('GET', '/mot-de-passe-oublie');
        $form = $crawler->filter('form')->form([
            'forgot_password_request_form[email]' => 'reset@example.com',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/login');

        $reloadedUser = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'reset@example.com']);
        self::assertNotNull($reloadedUser);
        self::assertNotNull($reloadedUser->getResetPasswordToken());
        self::assertNotNull($reloadedUser->getResetPasswordExpiresAt());
    }
}
