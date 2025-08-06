<?php

declare(strict_types=1);

namespace FastrackGps\Tracking\Service;

use FastrackGps\Core\ValueObject\Coordinates;
use Psr\Log\LoggerInterface;

final class OpenStreetMapGeocodingService implements GeocodingServiceInterface
{
    private const NOMINATIM_URL = 'https://nominatim.openstreetmap.org';
    private const USER_AGENT = 'FastrackGPS/1.0';

    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public function reverseGeocode(Coordinates $coordinates): ?string
    {
        try {
            $url = sprintf(
                '%s/reverse?format=json&lat=%f&lon=%f&zoom=18&addressdetails=1',
                self::NOMINATIM_URL,
                $coordinates->latitude,
                $coordinates->longitude
            );

            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'User-Agent: ' . self::USER_AGENT,
                        'Accept: application/json'
                    ],
                    'timeout' => 10
                ]
            ]);

            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                return null;
            }

            $data = json_decode($response, true);
            if (!$data || !isset($data['display_name'])) {
                return null;
            }

            return $data['display_name'];

        } catch (\Exception $e) {
            $this->logger->warning('Geocoding failed', [
                'coordinates' => ['lat' => $coordinates->latitude, 'lng' => $coordinates->longitude],
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function geocode(string $address): ?Coordinates
    {
        try {
            $url = sprintf(
                '%s/search?format=json&q=%s&limit=1',
                self::NOMINATIM_URL,
                urlencode($address)
            );

            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'User-Agent: ' . self::USER_AGENT,
                        'Accept: application/json'
                    ],
                    'timeout' => 10
                ]
            ]);

            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                return null;
            }

            $data = json_decode($response, true);
            if (!$data || empty($data) || !isset($data[0]['lat'], $data[0]['lon'])) {
                return null;
            }

            return new Coordinates(
                (float) $data[0]['lat'],
                (float) $data[0]['lon']
            );

        } catch (\Exception $e) {
            $this->logger->warning('Forward geocoding failed', [
                'address' => $address,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function isServiceAvailable(): bool
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'User-Agent: ' . self::USER_AGENT
                    ],
                    'timeout' => 5
                ]
            ]);

            $response = @file_get_contents(self::NOMINATIM_URL . '/status', false, $context);
            return $response !== false;

        } catch (\Exception $e) {
            $this->logger->debug('Geocoding service unavailable', ['error' => $e->getMessage()]);
            return false;
        }
    }
}