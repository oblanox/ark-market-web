<?php

namespace App\Service;

class Cart
{
    public static function init(): void
    {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public static function addItem(int $id, string $type, string $name, string $image, int $price, string $params = '', int $qty = 1): void
    {
        self::init();
        $key = $type . '_' . $id . '_' . md5($params); // уникальность по типу, id и параметрам

        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$key] = [
                'id'     => $id,
                'type'   => $type,
                'name'   => $name,
                'image'  => $image,
                'price'  => $price,
                'params' => $params,
                'qty'    => $qty
            ];
        }
    }

    public static function removeItem(string $key): void
    {
        self::init();
        unset($_SESSION['cart'][$key]);
    }

    public static function clear(): void
    {
        unset($_SESSION['cart']);
    }

    public static function getItems(): array
    {
        self::init();
        return $_SESSION['cart'];
    }

    public static function getTotalPrice(): int
    {
        self::init();
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['qty'];
        }
        return $total;
    }

    public static function getCount(): int
    {
        self::init();
        return count($_SESSION['cart']);
    }
}
