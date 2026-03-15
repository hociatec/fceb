<?php

namespace App\Tests\Web;

class SecurityGuardsTest extends DatabaseWebTestCase
{
    public function testApiMeRequiresAuthentication(): void
    {
        $this->client->request('GET', '/api/me');

        self::assertResponseRedirects('/login');
    }

    public function testAuthenticatedUserCanReadApiMe(): void
    {
        $user = $this->createUser('member@example.com', 'Secret123!', ['ROLE_USER'], 'Member User');
        $this->client->loginUser($user);
        $this->client->request('GET', '/api/me');

        self::assertResponseIsSuccessful();
        self::assertJson($this->client->getResponse()->getContent());
        self::assertStringContainsString('member@example.com', $this->client->getResponse()->getContent());
    }

    public function testLogoutRejectsGetRequests(): void
    {
        $this->client->request('GET', '/logout');

        self::assertResponseStatusCodeSame(405);
    }
}
