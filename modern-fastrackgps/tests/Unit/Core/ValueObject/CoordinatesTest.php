<?php

declare(strict_types=1);

namespace Tests\Unit\Core\ValueObject;

use FastrackGps\Core\Exception\ValidationException;
use FastrackGps\Core\ValueObject\Coordinates;
use PHPUnit\Framework\TestCase;

final class CoordinatesTest extends TestCase
{
    public function testCanCreateValidCoordinates(): void
    {
        $coordinates = new Coordinates(40.7128, -74.0060); // New York

        $this->assertEquals(40.7128, $coordinates->latitude);
        $this->assertEquals(-74.0060, $coordinates->longitude);
    }

    public function testCanCreateFromArray(): void
    {
        $data = ['latitude' => 51.5074, 'longitude' => -0.1278]; // London
        $coordinates = Coordinates::fromArray($data);

        $this->assertEquals(51.5074, $coordinates->latitude);
        $this->assertEquals(-0.1278, $coordinates->longitude);
    }

    public function testToArrayReturnsCorrectFormat(): void
    {
        $coordinates = new Coordinates(48.8566, 2.3522); // Paris
        $array = $coordinates->toArray();

        $expected = [
            'latitude' => 48.8566,
            'longitude' => 2.3522,
        ];

        $this->assertEquals($expected, $array);
    }

    public function testDistanceCalculation(): void
    {
        $newYork = new Coordinates(40.7128, -74.0060);
        $london = new Coordinates(51.5074, -0.1278);

        $distance = $newYork->distanceTo($london);

        // Distance between NYC and London is approximately 5585 km
        $this->assertGreaterThan(5580000, $distance); // meters
        $this->assertLessThan(5590000, $distance);
    }

    public function testThrowsExceptionForInvalidLatitude(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('between -90 and 90 degrees');

        new Coordinates(91.0, 0.0);
    }

    public function testThrowsExceptionForInvalidLongitude(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('between -180 and 180 degrees');

        new Coordinates(0.0, 181.0);
    }

    public function testThrowsExceptionWhenCreatingFromIncompleteArray(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('latitude, longitude');

        Coordinates::fromArray(['latitude' => 40.7128]);
    }

    /**
     * @dataProvider validCoordinatesProvider
     */
    public function testAcceptsValidCoordinateRanges(float $lat, float $lng): void
    {
        $coordinates = new Coordinates($lat, $lng);
        
        $this->assertEquals($lat, $coordinates->latitude);
        $this->assertEquals($lng, $coordinates->longitude);
    }

    public static function validCoordinatesProvider(): array
    {
        return [
            'Equator and Prime Meridian' => [0.0, 0.0],
            'North Pole' => [90.0, 0.0],
            'South Pole' => [-90.0, 0.0],
            'International Date Line East' => [0.0, 180.0],
            'International Date Line West' => [0.0, -180.0],
        ];
    }
}