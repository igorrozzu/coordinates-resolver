<?php

namespace App\Tests\Unit;

use App\Service\Geocoder\Cache\GeocoderCacheInterface;
use App\Service\Geocoder\Exceptions\GeocoderException;
use App\Service\Geocoder\GeoBuilderInterface;
use App\Service\Geocoder\GeocoderBuilder;
use App\Service\Geocoder\GeocoderInterface;
use App\ValueObject\Address;
use App\ValueObject\Coordinates;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GeocoderBuilderTest extends KernelTestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    /**
     * @return void
     */
    public function testNotFoundProvidersParameter()
    {
        $builder = $this->getBuilder($this->getCacheMock(), $this->getLoggerMock());

        $this->expectException(GeocoderException::class);
        $this->expectExceptionMessage('Providers are not found');

        $builder->getLocation();
    }

    /**
     * @return void
     */
    public function testNotFoundAddressParameter()
    {
        $builder = $this->getBuilder($this->getCacheMock(), $this->getLoggerMock());
        $provider = $this->getProviderMock();
        $builder->setProviders([$provider]);

        $this->expectException(GeocoderException::class);
        $this->expectExceptionMessage('Address is not found');

        $builder->getLocation();
    }

    /**
     * @return void
     */
    public function testGettingLocationFromCache()
    {
        $testCoordinates = $this->getTestCoordinates();
        $address = $this->getAddressMock();
        $provider = $this->getProviderMock();
        $cache = $this->getCacheMock();

        $provider->expects($this->never())
            ->method('getLocation');
        $cache->expects($this->once())
            ->method('getLocation')
            ->willReturn($testCoordinates);
        $builder = $this->getBuilder($cache, $this->getLoggerMock());

        $coordinates = $builder->setAddress($address)
            ->setProviders([$provider])
            ->useCache(true)
            ->getLocation();

        $this->assertInstanceOf(Coordinates::class, $coordinates);
        $this->assertEquals($coordinates->getLat(), $testCoordinates->getLat());
        $this->assertEquals($coordinates->getLng(), $testCoordinates->getLng());
    }

    /**
     * @return void
     */
    public function testGettingLocationFromFirstProviderInSequence()
    {
        $testCoordinates = $this->getTestCoordinates();
        $address = $this->getAddressMock();
        $provider1 = $this->getProviderMock();
        $provider2 = $this->getProviderMock();
        $cache = $this->getCacheMock();

        $provider1->expects($this->once())
            ->method('getLocation')
            ->with($address)
            ->willReturn($testCoordinates);
        $provider2->expects($this->never())
            ->method('getLocation');
        $cache->expects($this->once())
            ->method('saveLocation')
            ->with($address, $testCoordinates);
        $builder = $this->getBuilder($cache, $this->getLoggerMock());

        $coordinates = $builder->setAddress($address)
            ->setProviders([$provider1, $provider2])
            ->useCache(false)
            ->getLocation();

        $this->assertInstanceOf(Coordinates::class, $coordinates);
        $this->assertEquals($coordinates->getLat(), $testCoordinates->getLat());
        $this->assertEquals($coordinates->getLng(), $testCoordinates->getLng());
    }

    /**
     * @return void
     */
    public function testGettingLocationFromSecondProviderInSequence()
    {
        $testCoordinates = $this->getTestCoordinates();
        $address = $this->getAddressMock();
        $provider1 = $this->getProviderMock();
        $provider2 = $this->getProviderMock();
        $cache = $this->getCacheMock();

        $provider1->expects($this->once())
            ->method('getLocation')
            ->with($address)
            ->willReturn(null);
        $provider2->expects($this->once())
            ->method('getLocation')
            ->with($address)
            ->willReturn($testCoordinates);
        $cache->expects($this->once())
            ->method('saveLocation')
            ->with($address, $testCoordinates);
        $builder = $this->getBuilder($cache, $this->getLoggerMock());

        $coordinates = $builder->setAddress($address)
            ->setProviders([$provider1, $provider2])
            ->useCache(false)
            ->getLocation();

        $this->assertInstanceOf(Coordinates::class, $coordinates);
        $this->assertEquals($coordinates->getLat(), $testCoordinates->getLat());
        $this->assertEquals($coordinates->getLng(), $testCoordinates->getLng());
    }

    /**
     * @return void
     */
    public function testGettingLocationFromSecondProviderInSequenceDueFirstProviderFailure()
    {
        $testCoordinates = $this->getTestCoordinates();
        $address = $this->getAddressMock();
        $provider1 = $this->getProviderMock();
        $provider2 = $this->getProviderMock();
        $cache = $this->getCacheMock();
        $logger = $this->getLoggerMock();

        $exception = new \Exception('Provider1 server error');
        $provider1->expects($this->once())
            ->method('getLocation')
            ->with($address)
            ->willThrowException($exception);
        $provider2->expects($this->once())
            ->method('getLocation')
            ->with($address)
            ->willReturn($testCoordinates);
        $cache->expects($this->once())
            ->method('saveLocation')
            ->with($address, $testCoordinates);
        $logger->expects($this->once())
            ->method('error')
            ->with('Provider1 server error', ['exception' => $exception]);
        $builder = $this->getBuilder($cache, $logger);

        $coordinates = $builder->setAddress($address)
            ->setProviders([$provider1, $provider2])
            ->useCache(false)
            ->getLocation();

        $this->assertInstanceOf(Coordinates::class, $coordinates);
        $this->assertEquals($coordinates->getLat(), $testCoordinates->getLat());
        $this->assertEquals($coordinates->getLng(), $testCoordinates->getLng());
    }

    /**
     * @return void
     */
    public function testNotFoundLocationFromCacheAndAllProviders()
    {
        $address = $this->getAddressMock();
        $provider1 = $this->getProviderMock();
        $provider2 = $this->getProviderMock();
        $cache = $this->getCacheMock();

        $provider1->expects($this->once())
            ->method('getLocation')
            ->with($address)
            ->willReturn(null);
        $provider2->expects($this->once())
            ->method('getLocation')
            ->with($address)
            ->willReturn(null);
        $cache->expects($this->once())
            ->method('getLocation')
            ->willReturn(null);
        $cache->expects($this->once())
            ->method('saveLocation')
            ->with($address);
        $builder = $this->getBuilder($cache, $this->getLoggerMock());

        $coordinates = $builder->setAddress($address)
            ->setProviders([$provider1, $provider2])
            ->useCache(true)
            ->getLocation();

        $this->assertNull($coordinates);
    }

    /**
     * @param MockObject $cache
     * @param MockObject $logger
     * @return GeoBuilderInterface
     */
    private function getBuilder(MockObject $cache, MockObject $logger): GeoBuilderInterface
    {
        return new GeocoderBuilder($cache, $logger);
    }

    /**
     * @return MockObject
     */
    private function getCacheMock(): MockObject
    {
        return $this->createMock(GeocoderCacheInterface::class);
    }

    /**
     * @return MockObject
     */
    private function getLoggerMock(): MockObject
    {
        return $this->createMock(LoggerInterface::class);
    }

    /**
     * @return MockObject
     */
    private function getProviderMock(): MockObject
    {
        return $this->createMock(GeocoderInterface::class);
    }

    /**
     * @return MockObject
     */
    private function getAddressMock(): MockObject
    {
        return $this->createMock(Address::class);
    }

    /**
     * @return Coordinates
     */
    private function getTestCoordinates(): Coordinates
    {
        return new Coordinates(1.0, 2.0);
    }
}