<?php

namespace App\Tests\Web;

class AdminAccessTest extends DatabaseWebTestCase
{
    public function testAnonymousUserIsRedirectedFromAdminToLogin(): void
    {
        $this->client->request('GET', '/admin');

        self::assertResponseRedirects('/login');
    }

    public function testRegularUserCannotAccessAdminDashboard(): void
    {
        $user = $this->createUser('user@example.com', 'Secret123!', ['ROLE_USER'], 'Regular User');
        $this->client->loginUser($user);
        $this->client->request('GET', '/admin');

        self::assertResponseStatusCodeSame(403);
    }

    public function testEditorCanAccessAdminDashboard(): void
    {
        $editor = $this->createUser('editor@example.com', 'Secret123!', ['ROLE_EDITOR'], 'Editor User');
        $this->client->loginUser($editor);
        $crawler = $this->client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Tableau de bord', $crawler->html());
        self::assertStringNotContainsString('Utilisateurs', $crawler->html());
    }

    public function testAdminSeesUserManagementLinkInDashboard(): void
    {
        $admin = $this->createUser('admin@example.com', 'Secret123!', ['ROLE_ADMIN'], 'Admin User');
        $this->client->loginUser($admin);
        $crawler = $this->client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Utilisateurs', $crawler->html());
    }
}
