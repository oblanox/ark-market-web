<?php

namespace App\Config;

class Config
{
    public const ADMIN_STEAM_ID = '101';

    // Пример будущих статусов доставки
    public const ORDER_PENDING = 0;
    public const ORDER_DELIVERED = 1;

    // Пример типов товаров
    public const TYPE_DINO = 'dino';
    public const TYPE_ITEM = 'item';
    public const TYPE_SERVICE = 'service';

    public const QUALITY_LABELS  = [
        'Primitive'    => 'Примитивное',
        'Ramshackle'   => 'Хлипкое',
        'Apprentice'   => 'Ученическое',
        'Journeyman'   => 'Подмастерье',
        'Mastercraft'  => 'Мастерское',
        'Ascendant'    => 'Высшее'
    ];
}
