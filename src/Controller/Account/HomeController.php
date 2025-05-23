<?php

namespace App\Controller\Account;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/compte')]
final class HomeController extends AbstractController
{

    #[Route('/', name: 'app_account')]
    public function index(OrderRepository $orderRepository): Response
    {

        $orders = $orderRepository->findBy([
            'user' => $this->getUser(),
            'state' =>  [Order::PAID, Order::SHIPPED]
        ]);

        return $this->render('account/index.html.twig', [
            'orders' => $orders
        ]);
    }
}
