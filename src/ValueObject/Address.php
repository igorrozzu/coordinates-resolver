<?php

declare(strict_types=1);

namespace App\ValueObject;
use Symfony\Component\Validator\Constraints as Assert;

class Address
{
    /**
     * @Assert\NotBlank(message="Country code is required")
     * @Assert\Length(min=2, max=3, minMessage="Country code must be at least 2 characters long", maxMessage="Country code cannot be longer than 3 characters")
     */
    private string $country;
    /**
     * @Assert\NotBlank(message="City is required")
     * @Assert\Length(max=255, maxMessage="City cannot be longer than 255 characters")
     */
    private string $city;
    /**
     * @Assert\NotBlank(message="Street code is required")
     * @Assert\Length(max=255, maxMessage="Street code cannot be longer than 255 characters")
     */
    private string $street;
    /**
     * @Assert\NotBlank(message="Postcode is required")
     * @Assert\Length(max=16, maxMessage="Postcode cannot be longer than 16 characters")
     */
    private string $postcode;

    /**
     * @param string $country
     * @param string $city
     * @param string $street
     * @param string $postcode
     */
    public function __construct(string $country, string $city, string $street, string $postcode)
    {
        $this->country = $country;
        $this->city = $city;
        $this->street = $street;
        $this->postcode = $postcode;
    }

    /**
     * @param array<string> $params
     * @return self
     */
    public static function fromArray(array $params): self
    {
        return new self(
            $params['country_code'] ?? '',
            $params['city'] ?? '',
            $params['street'] ?? '',
            $params['postcode'] ?? '',
        );
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @return string
     */
    public function getPostcode(): string
    {
        return $this->postcode;
    }
}
