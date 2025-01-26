<?php
namespace App\Enum;

enum Status: string
{
    case AVAILABLE = 'available';
    case NOT_AVAILABLE = 'not available';
    case OUT_OF_STOCK = 'out_of_stock';
}
