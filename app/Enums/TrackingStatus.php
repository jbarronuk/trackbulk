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

    public static function map(int $value): string
    {
        return match (self::from($value)) {
            self::Unknown => 'Unknown',
            self::Created => 'Created',
            self::Queued => 'Queued',
            self::Querying => 'Querying',
            self::RoyalMailReadyForDelivery => 'Ready for Delivery',
            self::RoyalMailDelivered => 'Delivered',
        };
    }
    public static function all()
    {
        return [
            self::Unknown->value => 'Unknown',
            self::Created->value => 'Created',
            self::Queued->value => 'Queued',
            self::Querying->value => 'Querying',
            self::RoyalMailReadyForDelivery->value => 'Ready for Delivery',
            self::RoyalMailDelivered->value => 'Delivered',
        ];
    }
}
