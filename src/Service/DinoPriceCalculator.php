<?php

namespace App\Service;

use App\Config\Config;

class DinoPriceCalculator
{
    /**
     * Рассчитывает финальную цену динозавра по параметрам
     */
    public static function calculate(int $basePrice, bool $xp = false, string $gender = 'random', bool $neutered = false): int
    {
        $price = $basePrice;

        // XP максимум
        if ($xp) {
            $price += $basePrice * (Config::PRICE_PERCENT['XP'] / 100);
        }

        // Пол: Male
        if ($gender !== 'random') {
            $price += $basePrice * (Config::PRICE_PERCENT['MALE'] / 100);
        }

        // Кастрат (если не кастрат — то дороже!)
        if (!$neutered) {
            $price += $basePrice * (Config::PRICE_PERCENT['NEUTERED'] / 100);
        }

        return (int) round($price);
    }
}
