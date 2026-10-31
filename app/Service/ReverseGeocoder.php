<?php

namespace Vici\Service;

use Vici\DB\DBConnector;
use Vici\Model\Site\Toponym;
use PDO;

class ReverseGeocoder
{
    private DBConnector $geoDb;
    private float $lat;
    private float $lng;
    private ?string $countryCode = null;
    private array $countryName = [];
    private ?string $nearbyPlace = null;

    public function __construct(float $lat, float $lng)
    {
        $this->geoDb = self::sharedGeoDb();
        $this->lat = $lat;
        $this->lng = $lng;
        $this->retrieveCountryCode();
    }

    // one PDO connection per request — a geocoder per site would otherwise
    // exhaust max_connections on pages listing many sites
    private static function sharedGeoDb(): \Vici\DB\DBConnector
    {
        static $geoDb = null;
        return $geoDb ??= new \Vici\DB\DBConnector('GEO');
    }

    public function getCountryCode(): string
    {
        return $this->countryCode ?? '';
    }

    public function getCountryName(string $lang): string
    {
        if (!isset($this->countryName[$lang])) {
            $this->retrieveCountryName($lang);
        }
        return $this->countryName[$lang];
    }

    /**
     * Localized country name for an ISO code, cached per request.
     * Shared by live resolution and stored toponyms (points.pnt_country).
     */
    public static function countryNameFor(string $code, string $lang): string
    {
        if ($code === '') {
            return '';
        }
        static $cache = [];
        $key = strtolower($code) . ':' . $lang;
        if (!array_key_exists($key, $cache)) {
            $stmt = self::sharedGeoDb()->prepare(
                "SELECT $lang AS country_intl FROM countries WHERE alpha_2 = :code LIMIT 1;"
            );
            $stmt->execute(['code' => strtolower($code)]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $cache[$key] = ($row && !empty($row['country_intl'])) ? $row['country_intl'] : strtoupper($code);
        }
        return $cache[$key];
    }

    public function getNearbyPlace(): string
    {
        if ($this->nearbyPlace === null) {
            $this->retrieveNearbyPlace();
        }
        return $this->nearbyPlace ?? '';
    }

    public function resolveToponym(string $lang = 'en'): array
    {
        return [
            'countryName' => [$lang => $this->getCountryName($lang)],
            'placeName' => [$lang => $this->getNearbyPlace($lang)]
        ];
    }

    private function retrieveCountryCode(): void
    {
        $sql = "SELECT country FROM geo_allcountries
            WHERE MBRIntersects(geo_point, ST_GeomFromText(:poly))
                AND fclass <> 'A'
                AND country <> ''
            ORDER BY
                acos(cos(radians(:lat1))*cos(radians(latitude))*cos(radians(longitude)-radians(:lng1))+sin(radians(:lat2))*sin(radians(latitude)))
            LIMIT 1;";
        $stmt = $this->geoDb->prepare($sql);
        $stmt->execute([
            'poly' => $this->boundingPolygon(0.5),
            'lat1' => $this->lat,
            'lng1' => $this->lng,
            'lat2' => $this->lat
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->countryCode = $row['country'] ?? '';
    }

    private function retrieveCountryName(string $lang): void
    {
        $this->countryName[$lang] = self::countryNameFor($this->countryCode ?? '', $lang);
    }

    private function retrieveNearbyPlace(string $lang = 'en'): void
    {
        $sql = "SELECT name FROM geo_allcountries
            WHERE MBRIntersects(geo_point, ST_GeomFromText(:poly))
                AND fclass = 'P'
                AND fcode <> 'PPLX'
                AND fcode <> 'PPLCH'
                AND fcode <> 'PPLH'
            ORDER BY
                acos(cos(radians(:lat1))*cos(radians(latitude))*cos(radians(longitude)-radians(:lng1))+sin(radians(:lat2))*sin(radians(latitude))) / log10(population + 50)
            LIMIT 1;";
        $stmt = $this->geoDb->prepare($sql);
        $stmt->execute([
            'poly' => $this->boundingPolygon(0.5),
            'lat1' => $this->lat,
            'lng1' => $this->lng,
            'lat2' => $this->lat
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->nearbyPlace = $row['name'] ?? '';
    }

    private function boundingPolygon(float $halfDeg): string
    {
        // WKT polygon as (lng, lat) pairs — matches geo_point = POINT(lng, lat)
        $w = $this->lng - $halfDeg;
        $e = $this->lng + $halfDeg;
        $s = $this->lat - $halfDeg;
        $n = $this->lat + $halfDeg;
        return sprintf(
            'POLYGON((%F %F,%F %F,%F %F,%F %F,%F %F))',
            $w, $s, $e, $s, $e, $n, $w, $n, $w, $s
        );
    }
}
