<?php

namespace App\Controller\Account;

use App\Classe\Cart;
use App\Entity\Address;
use App\Form\AddressUserTypeForm;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/compte')]
final class AddressController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $manager,
    )
    {
    }

    #[Route('/adresses', name: 'app_account_address')]
    public function addresses(): Response
    {
        return $this->render('account/address/index.html.twig');
    }

    #[Route('/adresses/delete/{id}', name: 'app_account_address_delete')]
    public function delete(int $id, AddressRepository $addressRepository): Response
    {
        $address = $addressRepository->find($id);

        if (!$address OR ($address->getUser() !== $this->getUser())) {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer cette adresse');
            return $this->redirectToRoute('app_account_address');
        }

        $this->manager->remove($address);
        $this->manager->flush();

        $this->addFlash('success', 'L\'adresse à bien été supprimé');

        return $this->redirectToRoute('app_account_address');
    }

    #[Route('/adresse/ajouter/{id}', name: 'app_account_address_form', defaults: ['id' => null])]
    public function form($id, Request $request, AddressRepository $addressRepository, Cart $cart): Response
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

            if ($cart->fullQty() > 0) {
                return $this->redirectToRoute('app_order');
            }

            return $this->redirectToRoute('app_account_address');
        }

        return $this->render('account/address/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
