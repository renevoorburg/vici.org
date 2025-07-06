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
        $this->geoDb = new \Vici\DB\DBConnector('GEO');
        $this->lat = $lat;
        $this->lng = $lng;
        $this->retrieveCountryCode();
    }

    public function getCountryName(string $lang): string
    {
        if (!isset($this->countryName[$lang])) {
            $this->retrieveCountryName($lang);
        }
        return $this->countryName[$lang];
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
            WHERE 
                latitude < :lat_plus AND
                latitude > :lat_min AND
                longitude < :lng_plus AND
                longitude > :lng_min
                AND fclass <> 'A'
                AND country <> ''
            ORDER BY 
                acos(cos(radians(:lat1))*cos(radians(latitude))*cos(radians(longitude)-radians(:lng1))+sin(radians(:lat2))*sin(radians(latitude)))
            LIMIT 1;";
        $stmt = $this->geoDb->prepare($sql);
        $stmt->execute([
            'lat_plus' => $this->lat + 0.5,
            'lat_min' => $this->lat - 0.5,
            'lng_plus' => $this->lng + 0.5,
            'lng_min' => $this->lng - 0.5,
            'lat1' => $this->lat,
            'lng1' => $this->lng,
            'lat2' => $this->lat
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->countryCode = $row['country'] ?? '';
    }

    private function retrieveCountryName(string $lang): void
    {
        if ($this->countryCode) {
            $sql = "SELECT $lang AS country_intl FROM countries WHERE alpha_2 = :code LIMIT 1;";
            $stmt = $this->geoDb->prepare($sql);
            $stmt->execute(['code' => strtolower($this->countryCode)]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['country_intl'])) {
                $this->countryName[$lang] = $row['country_intl'];
            } else {
                $this->countryName[$lang] = $this->countryCode;
            }
        } else {
            $this->countryName[$lang] = '';
        }
    }

    private function retrieveNearbyPlace(string $lang = 'en'): void
    {
        $sql = "SELECT name FROM geo_allcountries
            WHERE 
                latitude < :lat_plus
                AND latitude > :lat_min
                AND longitude < :lng_plus
                AND longitude > :lng_min
                AND fclass = 'P'
                AND fcode <> 'PPLX'
                AND fcode <> 'PPLCH'
                AND fcode <> 'PPLH'
            ORDER BY 
                acos(cos(radians(:lat1))*cos(radians(latitude))*cos(radians(longitude)-radians(:lng1))+sin(radians(:lat2))*sin(radians(latitude))) / log10(population + 50)
            LIMIT 1;";
        $stmt = $this->geoDb->prepare($sql);
        $stmt->execute([
            'lat_plus' => $this->lat + 0.5,
            'lat_min' => $this->lat - 0.5,
            'lng_plus' => $this->lng + 0.5,
            'lng_min' => $this->lng - 0.5,
            'lat1' => $this->lat,
            'lng1' => $this->lng,
            'lat2' => $this->lat
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->nearbyPlace = $row['name'] ?? '';
    }
}
