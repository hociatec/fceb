<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_auth_', format: 'json')]
class AuthController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload) || empty($payload['email']) || empty($payload['password']) || empty($payload['full_name'])) {
            return $this->json(['message' => 'email, password et full_name sont requis.'], 422);
        }

        if ($entityManager->getRepository(User::class)->findOneBy(['email' => mb_strtolower($payload['email'])])) {
            return $this->json(['message' => 'Cette adresse e-mail existe déjà.'], 409);
        }

        $user = (new User())
            ->setEmail($payload['email'])
            ->setFullName($payload['full_name'])
            ->setRoles(['ROLE_USER']);
        $user->setPassword($passwordHasher->hashPassword($user, $payload['password']));

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'message' => 'Compte créé.',
            'user' => [
                'email' => $user->getEmail(),
                'full_name' => $user->getFullName(),
            ],
        ], 201);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json([
            'authenticated' => true,
            'user' => [
                'email' => $user->getEmail(),
                'full_name' => $user->getFullName(),
                'roles' => $user->getRoles(),
            ],
        ]);
    }
}
