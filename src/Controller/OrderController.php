<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\Address;
use App\Entity\Carrier;
use App\Entity\OrderDetail;
use App\Form\OrderTypeForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted("ROLE_USER")]
#[Route('/commande')]
final class OrderController extends AbstractController
{
    /*
     * 1ère étape du tunnel d'achat
     * Choix de l'adresse de livraison et du transporteur
     */
    #[Route('/livraison', name: 'app_order')]
    public function index(): Response
    {
        $addresses = $this->getUser()->getAddresses();

        if (count($addresses) === 0) {
            return $this->redirectToRoute('app_account_address_form');
        }

        $deliveryForm = $this->createForm(OrderTypeForm::class, null, [
            'addresses' => $addresses,
            'action' => $this->generateUrl('app_order_summary')
        ]);

        return $this->render('order/index.html.twig', [
            'deliveryForm' => $deliveryForm->createView()
        ]);
    }

    /*
     * 2ème étape du tunel d'achat
     * Récap de la commande de l'utilisateur
     * Insertion en bdd
     * Préparation du paiement vers Stripe
     */
    #[Route('/recapitulatif', name: 'app_order_summary')]
    public function add(Request $request, Cart $cart, EntityManagerInterface $manager): Response
    {

        if ($request->getMethod() != 'POST') {
            return $this->redirectToRoute('app_cart');
        }

        $products = $cart->getCart();

        $deliveryForm = $this->createForm(OrderTypeForm::class, null, [
            'addresses' => $this->getUser()->getAddresses(),
        ]);
        $deliveryForm->handleRequest($request);

        if ($deliveryForm->isSubmitted() && $deliveryForm->isValid()) {
            /** @var Carrier $carriers */
            $carriers = $deliveryForm->get('carriers')->getData();

            /** @var Address $address */
            $address = $deliveryForm->get('addresses')->getData();

            $addressDetail = $address->getFirstname() . ' ' . $address->getLastname() . '<br>';
            $addressDetail .= $address->getAddress() . '<br>';
            $addressDetail .= $address->getPostal() . ' ' . $address->getCity() . '<br>';
            $addressDetail .= $address->getCountry() . '<br>';
            $addressDetail .= $address->getPhone() . '<br>';

            $order = new Order();
            $order
                ->setCreatedAt(new \DateTime())
                ->setState(Order::STATE[Order::PENDING_DEBIT])
                ->setCarrierPrice($carriers->getPrice())
                ->setCarrierName($carriers->getName())
                ->setDelivery($addressDetail)
                ->setUser($this->getUser())
            ;

            foreach ($products as $product) {
                $orderDetail = new OrderDetail();
                $orderDetail
                    ->setProductName($product['object']->getName())
                    ->setProductIllustration($product['object']->getIllustration())
                    ->setProductPrice($product['object']->getPrice())
                    ->setProductQuantity($product['qty'])
                    ->setProductTva($product['object']->getTva())
                ;
                $order->addOrderDetail($orderDetail);
            }

            $manager->persist($order);
            $manager->flush();
        }

        return $this->render('order/summary.html.twig', [
            'choices' => $deliveryForm->getData(), //Choix de l'utilisateur de l'étape 1
            'cart' => $products,
            'totalTtc' => $cart->getTotalTtc(),
        ]);
    }
}
