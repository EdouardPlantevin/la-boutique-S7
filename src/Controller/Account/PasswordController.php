<?php

namespace App\Controller\Account;

use App\Form\PasswordUserTypeForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/compte')]
final class PasswordController extends AbstractController
{

    #[Route('/modifier-mon-mot-de-passe', name: 'app_account_modify_password')]
    public function index(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(PasswordUserTypeForm::class, $this->getUser(), [
            'passwordHasher' => $passwordHasher,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            $this->addFlash('success', 'Votre mot de passe à bien été modifié');
            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/password/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
