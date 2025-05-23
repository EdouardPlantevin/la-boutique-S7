<?php

namespace App\Services;

class Tva
{
    static function getPriceTTC($tva, $price): float|int
    {
        $coeff = 1 + ($tva / 100);
        return $coeff * $price;
    }
}