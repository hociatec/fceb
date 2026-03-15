<?php

namespace App\Tests\Web;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Cache\CacheInterface;

abstract class DatabaseWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetDatabase();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->clearAppCache();
    }

    private function resetDatabase(): void
    {
        self::ensureKernelShutdown();
        self::bootKernel();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);

        if ([] !== $metadata) {
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        }

        $entityManager->clear();
        self::ensureKernelShutdown();
    }

    protected function persist(object $entity): object
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }

    protected function createUser(
        string $email = 'user@example.com',
        string $password = 'MotDePasse123!',
        array $roles = ['ROLE_USER'],
        string $fullName = 'Test User',
    ): User {
        $user = (new User())
            ->setFullName($fullName)
            ->setEmail($email)
            ->setRoles($roles);

        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user->setPassword($passwordHasher->hashPassword($user, $password));

        $this->persist($user);

        return $user;
    }

    protected function clearAppCache(): void
    {
        $cache = static::getContainer()->get('cache.app');

        if ($cache instanceof CacheInterface) {
            $cache->clear();
        }
    }
}
