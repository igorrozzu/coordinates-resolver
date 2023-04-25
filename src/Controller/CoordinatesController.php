<?php

declare(strict_types=1);

namespace App\Controller;

use App\Request\GeocodeRequest;
use App\Service\Geocoder\Exceptions\GeocoderException;
use App\Service\Geocoder\GeoBuilderInterface;
use App\Service\Geocoder\Providers\GoogleMapsGeocoder;
use App\Service\Geocoder\Providers\HereMapsGeocoder;
use App\ValueObject\Address;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Annotation\Route;

class CoordinatesController extends AbstractController
{
    /**
     * @var GeoBuilderInterface
     */
    private GeoBuilderInterface $geocoderBuilder;

    /***
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * @param GeoBuilderInterface $geocoderBuilder
     * @param ValidatorInterface $validator
     */
    public function __construct(GeoBuilderInterface $geocoderBuilder, ValidatorInterface $validator)
    {
        $this->geocoderBuilder = $geocoderBuilder;
        $this->validator = $validator;
    }

    /**
     * @Route(
     *     methods={"GET"},
     *     path="/coordinates",
     *     name="geocode",
     * )
     * @param GeocodeRequest $request
     * @param ContainerInterface $container
     * @return JsonResponse
     */
    public function geocodeAction(GeocodeRequest $request, ContainerInterface $container): JsonResponse
    {
        $errors = $this->validator->validate($request);
        if ($errors->count() > 0) {
            return $this->json($errors, 422);
        }
        $providers = array_map(fn($p) => $container->get('geocoder.' . $p), $request->providers);
        $coordinates = $this->geocoderBuilder
            ->setAddress($request->address)
            ->setProviders($providers)
            ->useCache($request->useCache)
            ->getLocation();

        return $this->json($coordinates);
    }

    /**
     * @Route(path="/gmaps", name="gmaps")
     * @param Request $request
     * @param GoogleMapsGeocoder $googleMapsGeocoder
     * @return JsonResponse
     * @throws GeocoderException
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function googleMapsAction(Request $request, GoogleMapsGeocoder $googleMapsGeocoder): JsonResponse
    {
        $address = Address::fromArray($request->query->all());
        $errors = $this->validator->validate($address);
        if ($errors->count() > 0) {
            return $this->json($errors, 422);
        }
        $location = $googleMapsGeocoder->getLocation($address);

        return $this->json($location);
    }

    /**
     * @Route(path="/hmaps", name="hmaps")
     * @param Request $request
     * @param HereMapsGeocoder $hereMapsGeocoder
     * @return JsonResponse
     * @throws GeocoderException
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function hereMapsAction(Request $request, HereMapsGeocoder $hereMapsGeocoder): JsonResponse
    {
        $address = Address::fromArray($request->query->all());
        $errors = $this->validator->validate($address);
        if ($errors->count() > 0) {
            return $this->json($errors, 422);
        }
        $location = $hereMapsGeocoder->getLocation($address);

        return $this->json($location);
    }
}