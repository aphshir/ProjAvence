<?php

namespace App\Enum;

enum ProductStatus: string
{
    case DISPONIBLE = 'disponible';
    case EN_RUPTURE = 'en_rupture';
    case EN_PRECOMMANDE = 'en_precommande';

    public function label(): string
    {
        return match($this) {
            self::DISPONIBLE => 'product_status.disponible',
            self::EN_RUPTURE => 'product_status.en_rupture',
            self::EN_PRECOMMANDE => 'product_status.en_precommande',
        };
    }
}
