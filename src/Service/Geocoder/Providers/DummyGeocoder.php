<?php

declare(strict_types=1);

namespace App\Service\Geocoder\Providers;

use App\Service\Geocoder\GeocoderInterface;
use App\ValueObject\Address;
use App\ValueObject\Coordinates;

/**
 *
 */
class DummyGeocoder implements GeocoderInterface
{
    /**
     * @param Address $address
     * @return Coordinates|null
     */
    public function getLocation(Address $address): ?Coordinates
    {
        return new Coordinates(1.0, 2.0);
    }
}