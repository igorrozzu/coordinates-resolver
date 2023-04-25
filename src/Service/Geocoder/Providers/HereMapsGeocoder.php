<?php

declare(strict_types=1);

namespace App\Service\Geocoder\Providers;

use App\Service\Geocoder\Geocoder;
use App\Service\Geocoder\GeocoderInterface;
use App\ValueObject\Address;
use App\ValueObject\Coordinates;

/**
 *
 */
class HereMapsGeocoder extends Geocoder implements GeocoderInterface
{
    /**
     * @var string
     */
    protected string $apiUri = 'https://geocode.search.hereapi.com/v1/geocode';

    /**
     * @param Address $address
     * @return array[]
     */
    protected function getRequestParams(Address $address): array
    {
        return [
            'query' => [
                'qq' => implode(';', [
                    "country={$address->getCountry()}",
                    "city={$address->getCity()}",
                    "street={$address->getStreet()}",
                    "postalCode={$address->getPostcode()}"
                ]),
                'apiKey' => $this->apiKey
            ]
        ];
    }

    /**
     * @param array[] $response
     * @return Coordinates|null
     */
    protected function processResponse(array $response): ?Coordinates
    {
        if (count($response['items']) === 0) {
            return null;
        }
        $firstItem = $response['items'][0];
        if ($firstItem['resultType'] !== 'houseNumber') {
            return null;
        }
        $position = $firstItem['position'];

        return new Coordinates($position['lat'], $position['lng']);
    }
}
