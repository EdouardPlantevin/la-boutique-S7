<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterUserTypeForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class RegisterController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function index(Request $request, EntityManagerInterface $manager): Response
    {

        if ($this->getUser()) {
            $this->addFlash('warning', 'Vous êtes déjà connecté');
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $from = $this->createForm(RegisterUserTypeForm::class, $user);
        $from->handleRequest($request);

        if ($from->isSubmitted() && $from->isValid()) {
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Merci pour votre inscription');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('register/index.html.twig', [
            'form' => $from->createView(),
        ]);
    }
}
