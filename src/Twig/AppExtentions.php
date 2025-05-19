<?php

namespace App\Twig;

use App\Classe\Cart;
use App\Repository\CategoryRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;

class AppExtentions extends AbstractExtension implements GlobalsInterface
{

    private CategoryRepository $categoryRepository;
    private Cart $cart;

    public function __construct(CategoryRepository $categoryRepository, Cart $cart)
    {
        $this->categoryRepository = $categoryRepository;
        $this->cart = $cart;
    }


    public function getFilters(): array
    {
        return [
            new TwigFilter('price', [$this, 'formatPrice']),
        ];
    }

    public function formatPrice($price): string
    {
        return number_format($price, 2, ',') . ' €';
    }

    public function getGlobals(): array
    {
        return [
            'allCategories' => $this->categoryRepository->findAll(),
            'cartQty' => $this->cart->fullQty()
        ];
    }

}