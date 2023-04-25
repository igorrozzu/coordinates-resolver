<?php

declare(strict_types=1);

namespace App\Service\Geocoder\Cache;

use App\Repository\ResolvedAddressRepository;
use App\ValueObject\Address;
use App\ValueObject\Coordinates;

/**
 *
 */
class GeocoderDBCache implements GeocoderCacheInterface
{
    /**
     * @var ResolvedAddressRepository
     */
    protected ResolvedAddressRepository $repository;

    /**
     * @param ResolvedAddressRepository $repository
     */
    public function __construct(ResolvedAddressRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param Address $address
     * @return Coordinates|null
     */
    public function getLocation(Address $address): ?Coordinates
    {
        $address = $this->repository->getByAddress($address);
        if (!$address) {
            return null;
        }

        return new Coordinates((float) $address->getLat(),(float) $address->getLng());
    }

    /**
     * @param Address $address
     * @param Coordinates|null $coordinates
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveLocation(Address $address, ?Coordinates $coordinates = null): void
    {
        $this->repository->saveIfNotExist($address, $coordinates);
    }
}
