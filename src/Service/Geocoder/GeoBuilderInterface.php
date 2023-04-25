<?php

declare(strict_types=1);

namespace App\Service\Geocoder;

use App\ValueObject\Address;
use App\ValueObject\Coordinates;

interface GeoBuilderInterface
{
    /**
     * @param Address $address
     * @return self
     */
    public function setAddress(Address $address): self;

    /**
     * @param array $providers
     * @return self
     */
    public function setProviders(array $providers): self;

    /**
     * @param bool $useCache
     * @return self
     */
    public function useCache(bool $useCache): self;

    /**
     * @return Coordinates|null
     */
    public function getLocation(): ?Coordinates;
}
