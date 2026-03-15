<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Model\RegistrationData;
use App\Form\RegistrationFormType;
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
        $data = new RegistrationData();
        $form = $this->createForm(RegistrationFormType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = (new User())
                ->setFullName((string) $data->fullName)
                ->setEmail((string) $data->email)
                ->setRoles(['ROLE_USER']);
            $user->setPassword($passwordHasher->hashPassword($user, (string) $data->plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre compte a bien été créé. Vous pouvez maintenant vous connecter.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            ...$this->siteContextBuilder->build(),
            'registrationForm' => $form->createView(),
        ]);
    }
}
