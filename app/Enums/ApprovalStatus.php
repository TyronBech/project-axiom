<?php

namespace App\Enums;

enum ApprovalStatus: string
{
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }

    public static function options(): array
    {
        return array_column(self::cases(), 'values');
    }
}
