<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\SiteContextBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    public function __construct(private readonly SiteContextBuilder $siteContextBuilder)
    {
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $error = null;

        if ($request->isMethod('POST')) {
            $fullName = trim((string) $request->request->get('full_name'));
            $email = trim((string) $request->request->get('email'));
            $password = (string) $request->request->get('password');

            if (!$fullName || !$email || !$password) {
                $error = 'Tous les champs sont obligatoires.';
            } elseif ($entityManager->getRepository(User::class)->findOneBy(['email' => mb_strtolower($email)])) {
                $error = 'Cette adresse e-mail existe deja.';
            } else {
                $user = (new User())
                    ->setFullName($fullName)
                    ->setEmail($email)
                    ->setRoles(['ROLE_USER']);
                $user->setPassword($passwordHasher->hashPassword($user, $password));

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/register.html.twig', [
            ...$this->siteContextBuilder->build(),
            'error' => $error,
        ]);
    }
}
