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
class GoogleMapsGeocoder extends Geocoder implements GeocoderInterface
{
    /**
     * @var string
     */
    protected string $apiUri = 'https://maps.googleapis.com/maps/api/geocode/json';

    /**
     * @param Address $address
     * @return array[]
     */
    protected function getRequestParams(Address $address): array
    {
        return [
            'query' => [
                'address' => $address->getStreet(),
                'components' => implode('|', [
                    "country:{$address->getCountry()}",
                    "locality:{$address->getCity()}",
                    "postal_code:{$address->getPostcode()}"
                ]),
                'key' => $this->apiKey
            ]
        ];
    }

    /**
     * @param array[] $response
     * @return Coordinates|null
     */
    protected function processResponse(array $response): ?Coordinates
    {
        if (count($response['results']) === 0) {
            return null;
        }
        $firstResult = $response['results'][0];
        if ($firstResult['geometry']['location_type'] !== 'ROOFTOP') {
            return null;
        }
        $location = $firstResult['geometry']['location'];

        return new Coordinates($location['lat'], $location['lng']);
    }
}