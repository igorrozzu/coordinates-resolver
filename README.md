# Coordinates resolver

## How the API works
API handles request on http://localhost/coordinates with the following params:
* country_code (required, string)
* city (required, string)
* street (required, string)
* postcode (required, string)
* providers (optional, array, default: ["google_maps"]). 
The sequence of providers for external requests for retrieval of coordinates.
Three providers are available: "google_maps", "here_maps" and "dummy" for tests.
They can be regulated in `.env` file, variable `GEOCODER_ENABLED_PROVIDERS`.
* use_cache (optional, bool, default: "false"). To use a layer of cache before requests to external providers

The service validates the request params according to fields described above. The validation errors will be sent in json format.
Default optional fields can be regulated using env variables in `.env` file.

Example request:
http://localhost/coordinates?country_code=lt&city=vilnius&street=jasinskio+16&postcode=01112&providers[]=google_maps&providers[]=here_maps&use_cache=true

Also API provides two additional endpoints just to check how providers work separately:
http://localhost/gmaps and http://localhost/hmaps, both with the following params:
* country_code (required, string)
* city (required, string)
* street (required, string)
* postcode (required, string)

## How to add a new provider
Providers are developed using Template Method design pattern, it should be pretty easy to add a new one:
1. Create a provider class with implementation of `GeocoderInterface` interface and put it in `src/Service/Geocoder/Providers` folder
2. Register provider in the service container under special short name with prefix `geocoder.` in `services.yaml` file
3. Enable created provider putting its short name without the prefix in `.env` file in variable `GEOCODER_ENABLED_PROVIDERS`
4. The new provider can be used by adding the name in get parameter `providers`

An example of a new provider:

src/Service/Geocoder/Providers/DummyGeocoder.php
```
class DummyGeocoder implements GeocoderInterface
{
    public function getLocation(Address $address): ?Coordinates
    {
        return new Coordinates(1.0, 2.0);
    }
}
```
services.yaml file
```
    ...
    geocoder.dummy:
        alias: App\Service\Geocoder\Providers\DummyGeocoder
        public: true
```
.env file
```
...
GEOCODER_ENABLED_PROVIDERS='["google_maps","here_maps","dummy"]'
```


## Tests
The service is covered with basic unit tests.
The results are available by running the following command inside the php container `nt_coordinates_resolver_php`:
```
./vendor/bin/phpunit
```
or
```
php bin/phpunit
```


## How to start project

These are following steps to setup project:

```
cp .env.dist .env
```

then inside of .env file, replace set correct values for GOOGLE_GEOCODING_API_KEY and HEREMAPS_GEOCODING_API_KEY variables, and those keys will be sent separately in the email. 

then prepare docker environment:
```
docker-compose build
docker-compose up -d
docker-compose run php bash
```

final project steps inside of docker container:
```
composer install
bin/console doctrine:database:create
bin/console doctrine:schema:create
```

then go to `http://localhost/coordinates` and it should return 

```
{"lat":55.90742079144914,"lng":21.135541627577837}
```

JSON. If you want to check different address, then add params to url: http://localhost/coordinates?countryCode=lithuania&city=vilnius&street=gedimino+9&postcode=12345 . 
