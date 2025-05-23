<?php

namespace App\Controller;

use App\Classe\Cart;
use Stripe\Stripe;
use App\Entity\Order;
use Stripe\Checkout\Session;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class PaymentController extends AbstractController
{

    public function __construct(
        private OrderRepository        $orderRepository,
        private EntityManagerInterface $manager
    )
    {
    }

    #[Route('/commande/paiement/{id_order}', name: 'app_payment')]
    public function index(int $id_order): Response
    {

        $order = $this->orderRepository->findOneBy([
            'id' => $id_order,
            'user' => $this->getUser(),
        ]);

        if (!$order) {
            throw $this->createAccessDeniedException();
        }

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $products_for_stripe = [];

        foreach ($order->getOrderDetails() as $product) {

            $products_for_stripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => number_format($product->getProductPriceWt() * 100, 0, '', ''),
                    'product_data' => [
                        'name' => $product->getProductName(),
                        'images' => [
                            $_ENV['DOMAIN'] . '/uploads/' . $product->getProductIllustration()
                        ]
                    ]
                ],
                'quantity' => $product->getProductQuantity(),
            ];
        }

        //Carrier
        $products_for_stripe[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $order->getCarrierPrice() * 100,
                'product_data' => [
                    'name' => 'Transporteur : ' . $order->getCarrierName(),
                    'description' => 'Votre transporteur vous tiendra au courant de l\'expédition',
                ]
            ],
            'quantity' => 1,
        ];

        try {
            $checkout_session = Session::create([
                'customer_email' => $this->getUser()->getEmail(),
                'line_items' => $products_for_stripe,
                'mode' => 'payment',
                'success_url' => $_ENV['DOMAIN'] . '/commande/merci/{CHECKOUT_SESSION_ID}',
                'cancel_url' => $_ENV['DOMAIN'] . '/mon-panier/annulation',
            ]);

            $order->setStripeSessionId($checkout_session->id);
            $this->manager->flush();
        } catch (ApiErrorException) {
            $this->addFlash('error', 'Une erreur s\'est produite lors du traitement');
            return $this->redirectToRoute('app_home');
        }


        return $this->redirect($checkout_session->url);
    }

    #[Route('/commande/merci/{stripe_session_id}', name: 'app_payment_success')]
    public function success($stripe_session_id, Cart $cart): Response
    {
        $order = $this->orderRepository->findOneBy([
            'stripe_session_id' => $stripe_session_id,
            'user' => $this->getUser(),
        ]);

        if (!$order) {
            throw $this->createAccessDeniedException();
        }

        if ($order->getState() == Order::PENDING_DEBIT) {
            $order->setState(Order::PAID);
            $this->manager->flush();

            $cart->remove();
        }

        return $this->render('payment/success.html.twig', [
            'order' => $order,
        ]);

    }
}
