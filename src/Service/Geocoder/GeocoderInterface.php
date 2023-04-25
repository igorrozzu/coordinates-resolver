<?php

declare(strict_types=1);

namespace App\Service\Geocoder;

use App\ValueObject\Address;
use App\ValueObject\Coordinates;

interface GeocoderInterface
{
    /**
     * @param Address $address
     * @return Coordinates|null
     */
    public function getLocation(Address $address): ?Coordinates;
}
