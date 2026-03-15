<?php

namespace App\Tests\Web;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthAndAccountTest extends DatabaseWebTestCase
{
    public function testLoginRedirectsToAccountForValidCredentials(): void
    {
        $this->createUser('member@example.com', 'Secret123!', ['ROLE_USER'], 'Member User');

        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->filter('form')->form([
            '_username' => 'member@example.com',
            '_password' => 'Secret123!',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/compte');
    }

    public function testAccountPageRequiresAuthentication(): void
    {
        $this->client->request('GET', '/compte');

        self::assertResponseRedirects('/login');
    }

    public function testAuthenticatedUserCanUpdateAccountProfile(): void
    {
        $user = $this->createUser('member@example.com', 'Secret123!', ['ROLE_USER'], 'Member User');
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/compte');
        $form = $crawler->selectButton('Enregistrer les modifications')->form([
            'account_profile_form[fullName]' => 'Compte Modifie',
            'account_profile_form[email]' => 'updated@example.com',
            'account_profile_form[newPassword][first]' => 'NouveauSecret123!',
            'account_profile_form[newPassword][second]' => 'NouveauSecret123!',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/compte');

        /** @var User $reloadedUser */
        $reloadedUser = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'updated@example.com']);
        self::assertNotNull($reloadedUser);
        self::assertSame('Compte Modifie', $reloadedUser->getFullName());
        self::assertTrue(
            static::getContainer()->get(UserPasswordHasherInterface::class)->isPasswordValid($reloadedUser, 'NouveauSecret123!')
        );
    }
}
