<?php

namespace App\Enums;

enum CompanyRole: string
{
    case Developer = 'developer';
    case TechLead = 'tech_lead';
    case Cto = 'cto';
    case ProductManager = 'product_manager';
    case Designer = 'designer';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Developer => 'Developer',
            self::TechLead => 'Tech lead',
            self::Cto => 'CTO',
            self::ProductManager => 'Product manager',
            self::Designer => 'Designer',
            self::Other => 'Altro',
        };
    }
}
