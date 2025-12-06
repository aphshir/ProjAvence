<?php

namespace App\Enum;

enum OrderStatus: string
{
    case EN_PREPARATION = 'en_preparation';
    case EXPEDIEE = 'expediee';
    case LIVREE = 'livree';
    case ANNULEE = 'annulee';

    public function label(): string
    {
        return match($this) {
            self::EN_PREPARATION => 'order_status.en_preparation',
            self::EXPEDIEE => 'order_status.expediee',
            self::LIVREE => 'order_status.livree',
            self::ANNULEE => 'order_status.annulee',
        };
    }
}
