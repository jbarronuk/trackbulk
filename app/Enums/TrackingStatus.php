<?php

namespace App\Enums;

use App\Enums\Extras\Labels;
use App\Enums\Extras\Values;

enum TrackingStatus: int
{
    use Labels, Values;

    case Unknown = 1;
    case Created = 2;
    case Queued = 3;
    case Querying = 4;
    case RoyalMailReadyForDelivery = 5;
    case RoyalMailDelivered = 6;

    public function label(): string
    {
        return match ($this) {
            self::Unknown => 'Unknown',
            self::Created => 'Created',
            self::Queued => 'Queued',
            self::Querying => 'Querying',
            self::RoyalMailReadyForDelivery => 'Ready for Delivery',
            self::RoyalMailDelivered => 'Delivered',
        };
    }
}
