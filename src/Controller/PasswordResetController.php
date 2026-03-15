<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\SiteContextBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class PasswordResetController extends AbstractController
{
    public function __construct(private readonly SiteContextBuilder $siteContextBuilder)
    {
    }

    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function requestReset(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $error = null;

        if ($request->isMethod('POST')) {
            $email = trim((string) $request->request->get('email'));

            if ('' === $email) {
                $error = 'Merci de renseigner votre adresse e-mail.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Merci de renseigner une adresse e-mail valide.';
            } else {
                $user = $userRepository->findOneBy(['email' => mb_strtolower($email)]);

                if ($user instanceof User) {
                    $token = bin2hex(random_bytes(32));
                    $user
                        ->setResetPasswordToken($token)
                        ->setResetPasswordExpiresAt(new \DateTimeImmutable('+2 hours'));

                    $entityManager->flush();

                    $resetUrl = $this->generateUrl('app_reset_password', ['token' => $token], 0);
                    $absoluteResetUrl = $request->getSchemeAndHttpHost().$resetUrl;

                    $message = (new Email())
                        ->from((string) $this->getParameter('app.contact_from_email'))
                        ->to($user->getEmail())
                        ->subject('Réinitialisation de votre mot de passe')
                        ->text(implode(PHP_EOL.PHP_EOL, [
                            'Une demande de réinitialisation de mot de passe a été effectuée.',
                            'Si vous êtes à l’origine de cette demande, utilisez ce lien :',
                            $absoluteResetUrl,
                            'Ce lien expire dans 2 heures.',
                        ]));

                    $mailer->send($message);
                }

                $this->addFlash('success', 'Si un compte correspond à cette adresse, un e-mail de réinitialisation vient d’être envoyé.');

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/forgot_password.html.twig', [
            ...$this->siteContextBuilder->build(),
            'error' => $error,
        ]);
    }

    #[Route('/reinitialiser-mon-mot-de-passe/{token}', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(
        string $token,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $userRepository->findOneByResetToken($token);
        $error = null;

        if (!$user instanceof User || !$user->getResetPasswordExpiresAt() || $user->getResetPasswordExpiresAt() < new \DateTimeImmutable()) {
            $this->addFlash('error', 'Ce lien de réinitialisation est invalide ou expiré.');

            return $this->redirectToRoute('app_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $password = (string) $request->request->get('password');
            $confirmPassword = (string) $request->request->get('confirm_password');

            if ('' === $password || '' === $confirmPassword) {
                $error = 'Merci de renseigner et confirmer le nouveau mot de passe.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Les deux mots de passe ne correspondent pas.';
            } elseif (mb_strlen($password) < 8) {
                $error = 'Le mot de passe doit contenir au moins 8 caractères.';
            } else {
                $user
                    ->setPassword($passwordHasher->hashPassword($user, $password))
                    ->setResetPasswordToken(null)
                    ->setResetPasswordExpiresAt(null);

                $entityManager->flush();

                $this->addFlash('success', 'Votre mot de passe a bien été mis à jour.');

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/reset_password.html.twig', [
            ...$this->siteContextBuilder->build(),
            'error' => $error,
        ]);
    }
}
