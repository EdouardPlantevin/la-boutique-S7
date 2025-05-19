<?php

namespace App\Classe;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class Cart
{
    public function __construct(private RequestStack $requestStack) {}

    public function add(Product $product): void
    {
        $cart = $this->requestStack->getSession()->get('cart');

        if (isset($cart[$product->getId()])) {
            $cart[$product->getId()] = [
                'object' => $product,
                'qty' => $cart[$product->getId()]['qty'] + 1
            ];
        } else {
            $cart[$product->getId()] = [
                'object' => $product,
                'qty' => 1
            ];
        }

        $this->requestStack->getSession()->set('cart', $cart);
    }

    public function decrease(int $id): void
    {
        $cart = $this->requestStack->getSession()->get('cart');

        if ($cart[$id]['qty'] > 1) {
            $cart[$id]['qty'] = $cart[$id]['qty'] - 1;
        } else {
            unset($cart[$id]);
        }

        $this->requestStack->getSession()->set('cart', $cart);
    }

    public function getCart(): array|null
    {
        return $this->requestStack->getSession()->get('cart');
    }

    public function remove(): void
    {
        $this->requestStack->getSession()->remove('cart');
    }

    public function fullQty(): int {

        $cart = $this->requestStack->getSession()->get('cart');
        $qty = 0;

        if(!isset($cart)) {
            return $qty;
        }
        foreach ($cart as $product) {
            $qty += $product['qty'];
        }
        return $qty;
    }

    public function getTotalTtc(): float
    {
        $cart = $this->requestStack->getSession()->get('cart');
        $price = 0;
        if(!isset($cart)) {
            return $price;
        }

        foreach ($cart as $product) {
            $price += $product['qty'] * $product['object']->getPriceTtc();
        }
        return $price;
    }
}
