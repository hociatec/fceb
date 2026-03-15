<?php

namespace App\Tests\Web;

use PHPUnit\Framework\Attributes\DataProvider;

class PublicPagesTest extends DatabaseWebTestCase
{
    #[DataProvider('publicPagesProvider')]
    public function testPublicPagesRespondSuccessfully(string $uri): void
    {
        $this->client->request('GET', $uri);

        self::assertResponseIsSuccessful();
    }

    public static function publicPagesProvider(): iterable
    {
        yield 'home' => ['/'];
        yield 'login' => ['/login'];
        yield 'register' => ['/register'];
        yield 'forgot password' => ['/mot-de-passe-oublie'];
        yield 'contact' => ['/contact'];
        yield 'articles' => ['/actualites'];
        yield 'calendar' => ['/calendrier'];
        yield 'ranking' => ['/classement'];
        yield 'players' => ['/effectif'];
        yield 'partners' => ['/partenaires'];
        yield 'join' => ['/rejoindre-le-club'];
        yield 'trial request' => ['/seance-decouverte'];
        yield 'volunteer' => ['/benevolat'];
        yield 'partner request' => ['/devenir-partenaire'];
        yield 'faq' => ['/faq'];
        yield 'training' => ['/entrainements'];
        yield 'access' => ['/acces'];
        yield 'staff' => ['/encadrement'];
        yield 'terms' => ['/cgu'];
        yield 'privacy' => ['/politique-de-confidentialite'];
    }
}

