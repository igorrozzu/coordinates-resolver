<?php

declare(strict_types=1);

namespace App\Request;
use App\ValueObject\Address;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;

class GeocodeRequest
{
    /**
     * @Assert\All(
     *     @Assert\Choice(callback="getEnabledProviders")
     * )
     */
    public array $providers;

    /**
     * @Assert\Type("boolean")
     */
    public bool $useCache;

    /**
     * @Assert\Valid()
     */
    public Address $address;

    /**
     * @param RequestStack $requestStack
     * @param ContainerInterface $container
     */
    public function __construct(RequestStack $requestStack, ContainerInterface $container)
    {
        $request = $requestStack->getCurrentRequest();
        $this->providers = $request->get('providers', $container->getParameter('geocoder_default_providers_sequence'));
        $this->useCache = $request->query->getBoolean('use_cache', $container->getParameter('geocoder_use_cache'));
        $this->address = Address::fromArray($request->query->all());
    }

    /**
     * @return mixed
     */
    public static function getEnabledProviders()
    {
        return json_decode($_ENV['GEOCODER_ENABLED_PROVIDERS']);
    }
}
