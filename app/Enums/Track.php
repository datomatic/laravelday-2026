<?php

namespace App\Enums;

enum Track: string
{
    case Ai = 'ai';
    case Backend = 'backend';
    case Frontend = 'frontend';
    case DevOps = 'devops';
    case Community = 'community';

    public function label(): string
    {
        return match ($this) {
            self::Ai => 'AI',
            self::Backend => 'Backend',
            self::Frontend => 'Frontend',
            self::DevOps => 'DevOps',
            self::Community => 'Community',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Ai => 'bg-violet-100 text-violet-700 ring-violet-200',
            self::Backend => 'bg-sky-100 text-sky-700 ring-sky-200',
            self::Frontend => 'bg-amber-100 text-amber-700 ring-amber-200',
            self::DevOps => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
            self::Community => 'bg-pink-100 text-pink-700 ring-pink-200',
        };
    }
}
