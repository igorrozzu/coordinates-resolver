<?php

declare(strict_types=1);

namespace App\Service\Geocoder;

use App\Service\Geocoder\Cache\GeocoderCacheInterface;
use App\Service\Geocoder\Exceptions\GeocoderException;
use App\ValueObject\Address;
use App\ValueObject\Coordinates;
use Exception;
use Psr\Log\LoggerInterface;

/**
 *
 */
class GeocoderBuilder implements GeoBuilderInterface
{
    /**
     * @var array<GeocoderInterface>
     */
    protected array $providers = [];

    /**
     * @var Address|null
     */
    protected ?Address $address = null;

    /**
     * @var bool
     */
    protected bool $useCache = false;

    /**
     * @var GeocoderCacheInterface
     */
    protected GeocoderCacheInterface $cache;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @param GeocoderCacheInterface $cache
     * @param LoggerInterface $logger
     */
    public function __construct(GeocoderCacheInterface $cache, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * @param Address $address
     * @return $this
     */
    public function setAddress(Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @param array<GeocoderInterface> $providers
     * @return $this
     */
    public function setProviders(array $providers): self
    {
        $this->providers = $providers;

        return $this;
    }

    /**
     * @param bool $useCache
     * @return $this
     */
    public function useCache(bool $useCache): self
    {
        $this->useCache = $useCache;

        return $this;
    }

    /**
     * @return Coordinates|null
     * @throws GeocoderException
     */
    public function getLocation(): ?Coordinates
    {
        if (empty($this->providers)) {
            throw new GeocoderException('Providers are not found');
        }
        if (!isset($this->address)) {
            throw new GeocoderException('Address is not found');
        }
        if ($this->useCache) {
            $coordinates = $this->cache->getLocation($this->address);
            if ($coordinates) {
                return $coordinates;
            }
        }
        foreach ($this->providers as $provider) {
            $coordinates = $this->findAndCacheLocationFromProvider($provider);
            if ($coordinates) {
                return $coordinates;
            }
        }
        $this->cache->saveLocation($this->address);

        return null;
    }

    /**
     * @param GeocoderInterface $provider
     * @return Coordinates|null
     */
    private function findAndCacheLocationFromProvider(GeocoderInterface $provider): ?Coordinates
    {
        try {
            $coordinates = $provider->getLocation($this->address);
            if ($coordinates) {
                $this->cache->saveLocation($this->address, $coordinates);
                return $coordinates;
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return null;
    }
}
