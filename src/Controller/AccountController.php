<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressUserTypeForm;
use App\Form\PasswordUserTypeForm;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/compte')]
final class AccountController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $manager,
    )
    {
    }

    #[Route('/', name: 'app_account')]
    public function index(): Response
    {
        return $this->render('account/index.html.twig');
    }

    #[Route('/modifier-mon-mot-de-passe', name: 'app_account_modify_password')]
    public function modifyPassword(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(PasswordUserTypeForm::class, $this->getUser(), [
            'passwordHasher' => $passwordHasher,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();
            $this->addFlash('success', 'Votre mot de passe à bien été modifié');
            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/adresses', name: 'app_account_address')]
    public function address(): Response
    {
        return $this->render('account/address.html.twig');
    }

    #[Route('/adresses/delete/{id}', name: 'app_account_address_delete')]
    public function addressDelete(int $id, AddressRepository $addressRepository): Response
    {
        $address = $addressRepository->find($id);

        if (!$address OR ($address->getUser() !== $this->getUser())) {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer cette adresse');
            return $this->redirectToRoute('app_account_address');
        }

        $this->manager->remove($address);
        $this->manager->flush();

        $this->addFlash('success', 'L\'adresse à bien été supprimé');

        return $this->render('account/address.html.twig');
    }

    #[Route('/adresse/ajouter/{id}', name: 'app_account_address_form', defaults: ['id' => null])]
    public function addressForm($id, Request $request, AddressRepository $addressRepository): Response
    {

        if ($id) {
            $address = $addressRepository->find($id);

            if (!$address OR $address->getUser() !== $this->getUser()) {
                $this->addFlash('danger', 'Vous ne pouvez pas modifier cette adresse');
                return $this->redirectToRoute('app_account_address');
            }
        } else {
            $address = new Address();
            $address->setUser($this->getUser());
        }

        $form = $this->createForm(AddressUserTypeForm::class, $address);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->manager->persist($address);
            $this->manager->flush();

            $this->addFlash('success', 'Votre adresse est correctement sauvegardée.');
            return $this->redirectToRoute('app_account_address');
        }

        return $this->render('account/address-form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
