<?php

namespace App\Controller\Account;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/compte')]
final class OrderController extends AbstractController
{
    #[Route('/commande/{id}', name: 'app_account_order')]
    public function index($id, OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->findOneBy([
            'id' => $id,
            'user' => $this->getUser(),
        ]);

        if (!$order) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('account/order/index.html.twig', [
            'order' => $order,
        ]);
    }
}
