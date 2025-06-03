<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Form\ForgotPasswordTypeForm;
use App\Form\ResetPasswordTypeForm;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ForgotPasswordController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $manager,
    ){}


    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password')]
    public function index(Request $request, UserRepository $userRepository): Response
    {

        $form = $this->createForm(ForgotPasswordTypeForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findOneBy(['email' => $form->get('email')->getData()]);

            $this->addFlash('success', 'Si votre adresse email existe, vous receverez un mail pour réinitialiser votre mot de passe.');

            if ($user) {

                $token = bin2hex(random_bytes(15));
                $user->setToken($token);
                $user->setTokenExpireAt(new \DateTime('+10 minutes'));
                $this->manager->flush();

                //Email de réinitialiser de mot de passe
                $mail = new Mail();
                $vars = [
                    'link' => $this->generateUrl('app_forgot_password_reset', [
                        'token' => $token,
                    ], UrlGeneratorInterface::ABSOLUTE_URL),
                ];
                $mail->send(
                    $user->getEmail(),
                    $user->getFullName(),
                    "Réinitialisation mot de passe",
                    Mail::FORGOT_PASSWORD,
                    $vars
                );
            }

        }

        return $this->render('password/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/mot-de-passe/reset/{token}', name: 'app_forgot_password_reset')]
    public function reset($token, Request $request, UserRepository $userRepository): Response
    {

        if (!$token) {
            return $this->redirectToRoute('app_forgot_password');
        }

        $user = $userRepository->findOneBy(['token' => $token]);

        if (!$user OR $user->getTokenExpireAt() < new \DateTime('now')) {
            $this->addFlash('danger', 'Le lien à expiré');
            return $this->redirectToRoute('app_forgot_password');
        }

        $form = $this->createForm(ResetPasswordTypeForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setToken(null)
                ->setTokenExpireAt(null);
            $this->manager->flush();
            $this->addFlash('success', 'Votre mot de passe à bien été modifié');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('password/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
