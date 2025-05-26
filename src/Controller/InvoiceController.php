<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

final class InvoiceController extends AbstractController
{

    /*
     * IMPRESSION FACTURE PDF pour un utilisateur connecté
     * Vérification de la commande pour un utilisateur donné
     */
    #[Route('/compte/facture/impression/{id_order}', name: 'app_invoice_customer')]
    public function printForCustomer(int $id_order, OrderRepository $orderRepository)
    {
        $order = $orderRepository->findOneBy([
            'id' => $id_order,
            'user' => $this->getUser(),
        ]);
        if (!$order) {
            return $this->redirectToRoute('app_account');
        }

        // instantiate and use the dompdf class
        $dompdf = new Dompdf();

        $html = $this->renderView('invoice/index.html.twig', [
            'order' => $order,
        ]);
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('invoice.pdf', [
            'Attachment' => false
        ]);

        exit();
    }


    /*
     * IMPRESSION FACTURE PDF pour un admin
     */
    #[Route('/admin/facture/impression/{id_order}', name: 'app_invoice_admin')]
    public function printForAdmin(int $id_order, OrderRepository $orderRepository)
    {
        $order = $orderRepository->find($id_order);
        if (!$order) {
            return $this->redirectToRoute('admin');
        }

        // instantiate and use the dompdf class
        $dompdf = new Dompdf();

        $html = $this->renderView('invoice/index.html.twig', [
            'order' => $order,
        ]);
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('invoice.pdf', [
            'Attachment' => false
        ]);

        exit();
    }
}
