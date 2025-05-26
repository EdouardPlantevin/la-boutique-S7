<?php

namespace App\Controller\Account;

use App\Entity\User;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/compte')]
final class WishlistController extends AbstractController
{
    #[Route('/liste-de-souhait', name: 'app_account_wishlist')]
    public function index(): Response
    {
        return $this->render('account/wishlist/index.html.twig');
    }

    #[Route('/liste-de-souhait/ajout/{slug}', name: 'app_account_wishlist_add')]
    public function add(string $slug, ProductRepository $productRepository, EntityManagerInterface $manager, Request $request): Response
    {

        $product = $productRepository->findOneBy(['slug' => $slug]);

        if (!$product) {
            $this->addFlash('info', 'Une erreur est survenue');
            return $this->redirect($request->headers->get('referer'));
        }

        $this->getUser()->addWishlist($product);
        $manager->flush();

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/liste-de-souhait/suppression/{slug}', name: 'app_account_wishlist_remove')]
    public function remove(string $slug, ProductRepository $productRepository, EntityManagerInterface $manager, Request $request): Response
    {
        $product = $productRepository->findOneBy(['slug' => $slug]);

        if (!$product) {
            $this->addFlash('info', 'Une erreur est survenue');
            return $this->redirectToRoute('app_account_wishlist');
        }

        $this->getUser()->removeWishlist($product);
        $manager->flush();

        return $this->redirect($request->headers->get('referer'));
    }
}
