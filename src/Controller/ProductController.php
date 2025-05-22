<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ProductController extends AbstractController
{
    #[Route('/produit/{slug}', name: 'app_product')]
    public function index(#[MapEntity(mapping: ['slug' => 'slug'])] Product $product): Response
    {
        return $this->render('product/index.html.twig', [
            'product' => $product
        ]);
    }
}
