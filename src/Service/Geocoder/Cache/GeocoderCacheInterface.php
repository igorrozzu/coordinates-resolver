<?php

declare(strict_types=1);

namespace App\Service\Geocoder\Cache;

use App\ValueObject\Address;;
use App\ValueObject\Coordinates;

/**
 *
 */
interface GeocoderCacheInterface
{
    /**
     * @param Address $address
     * @return Coordinates|null
     */
    public function getLocation(Address $address): ?Coordinates;

    /**
     * @param Address $address
     * @param Coordinates|null $coordinates
     * @return void
     */
    public function saveLocation(Address $address, ?Coordinates $coordinates = null): void;
}
