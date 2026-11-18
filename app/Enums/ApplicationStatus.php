<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case New = 'new';
    case Reviewing = 'reviewing';
    case Shortlisted = 'shortlisted';
    case Rejected = 'rejected';
    case Hired = 'hired';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
