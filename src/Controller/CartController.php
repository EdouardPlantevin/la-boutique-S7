<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/mon-panier')]
final class CartController extends AbstractController
{
    #[Route('/{motif}', name: 'app_cart', defaults: ['motif' => null])]
    public function index(Cart $cart, $motif): Response
    {

        if ($motif == "annulation") {
            $this->addFlash(
                'info',
                'Paiement annulé : Vous pouvez mettre à jour votre panier et votre commande.'
            );
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart->getCart(),
            'totalTtc' => $cart->getTotalTtc(),
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add')]
    public function add(int $id, Cart $cart, ProductRepository $productRepository, Request $request): Response
    {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException();
        }

        $cart->add($product);

        $this->addFlash('success', 'Produit correctement ajouté à votre panier.');

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/decrease/{id}', name: 'app_cart_decrease')]
    public function decrease(int $id, Cart $cart): Response
    {

        $cart->decrease($id);

        $this->addFlash('success', 'Produit correctement supprimé de votre panier.');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/remove', name: 'app_cart_remove')]
    public function remove(Cart $cart): Response
    {
        try {
            $cart->remove();
        } catch (\Exception $exception) {
            $this->addFlash('danger', 'Une erreur s\'est produite lors de la suppression.');
        }
        return $this->redirectToRoute('app_home');
    }
}
