<?php

declare(strict_types=1);

namespace App\Service\Geocoder;

use App\Service\Geocoder\Exceptions\GeocoderException;
use App\ValueObject\Address;
use App\ValueObject\Coordinates;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;

/**
 *
 */
abstract class Geocoder implements GeocoderInterface
{
    /**
     * @var string
     */
    protected string $apiUri;

    /**
     * @var string
     */
    protected string $apiKey;

    /**
     * @var ClientInterface
     */
    protected ClientInterface $client;

    /**
     * @param ClientInterface $client
     * @param string $apiKey
     */
    public function __construct(ClientInterface $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    /**
     * @param Address $address
     * @return Coordinates|null
     * @throws GuzzleException
     * @throws JsonException
     * @throws GeocoderException
     */
    public function getLocation(Address $address): ?Coordinates
    {
        if (empty($this->apiKey)) {
            throw new GeocoderException('Api key is not provided');
        }
        $response = $this->client->get($this->apiUri, $this->getRequestParams($address));
        $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        return $this->processResponse($data);
    }

    /**
     * @param Address $address
     * @return array
     */
    abstract protected function getRequestParams(Address $address): array;

    /**
     * @param array $response
     * @return Coordinates|null
     */
    abstract protected function processResponse(array $response): ?Coordinates;
}
