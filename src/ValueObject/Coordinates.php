<?php

declare(strict_types=1);

namespace App\ValueObject;

class Coordinates
{
    /**
     * @var float
     */
    private float $lat;
    /**
     * @var float
     */
    private float $lng;

    /**
     * @param float $lat
     * @param float $lng
     */
    public function __construct(float $lat, float $lng)
    {
        $this->lat = $lat;
        $this->lng = $lng;
    }

    /**
     * @return float
     */
    public function getLat(): float
    {
        return $this->lat;
    }

    /**
     * @return float
     */
    public function getLng(): float
    {
        return $this->lng;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'lat' => $this->lat,
            'lng' => $this->lng
        ];
    }
}
