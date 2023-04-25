<?php

namespace App\Repository;

use App\Entity\ResolvedAddress;
use App\ValueObject\Address;
use App\ValueObject\Coordinates;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ResolvedAddress|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResolvedAddress|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResolvedAddress[]    findAll()
 * @method ResolvedAddress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResolvedAddressRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResolvedAddress::class);
    }

    /**
     * @param Address $address
     * @return ResolvedAddress|null
     */
    public function getByAddress(Address $address): ?ResolvedAddress
    {
        return $this->findOneBy([
            'countryCode' => $address->getCountry(),
            'city' => $address->getCity(),
            'street' => $address->getStreet(),
            'postcode' => $address->getPostcode()
        ]);
    }

    /**
     * @param Address $address
     * @param Coordinates|null $coordinates
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveResolvedAddress(Address $address, ?Coordinates $coordinates): void
    {
        $resolvedAddress = new ResolvedAddress();
        $resolvedAddress
            ->setCountryCode($address->getCountry())
            ->setCity($address->getCity())
            ->setStreet($address->getStreet())
            ->setPostcode($address->getPostcode());

        if ($coordinates !== null) {
            $resolvedAddress
                ->setLat((string) $coordinates->getLat())
                ->setLng((string) $coordinates->getLng());
        }

        $entityManager = $this->getEntityManager();

        $entityManager->persist($resolvedAddress);
        $entityManager->flush();
    }

    /**
     * @param Address $address
     * @param Coordinates|null $coordinates
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveIfNotExist(Address $address, ?Coordinates $coordinates): void
    {
        $addressEntity = $this->getByAddress($address);
        if (!$addressEntity) {
            $this->saveResolvedAddress($address, $coordinates);
        }
    }
}
