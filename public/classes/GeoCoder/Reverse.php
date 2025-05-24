<?php

namespace GeoCoder;

class Reverse
{
    protected $connector, $lat, $lng;
    protected $country_code;
    protected Array $country_name;
    protected $nearby_place;

    public function __construct($connector, $lat, $lng)
    {
        $this->connector = $connector;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->retrieveCountryCode();
    }

    public function getCountryName($lang) : string
    {
        if (!isset($this->country_name[$lang])) $this->retrieveCountryName($lang);
        return $this->country_name[$lang];
    }

    public function getNearbyPlace() : string
    {
        if (!isset($this->nearby_place)) $this->retrieveNearbyPlace();
        return $this->nearby_place ? $this->nearby_place : '';
    }

    private function retrieveCountryCode() : void
    {
        $load = sys_getloadavg()[0];
        if ($load < 6.0) {
            $sql =
                "SELECT country FROM geo_allcountries
                WHERE 
                    latitude < " . $this->lat . " + 0.5 AND
                    latitude > " . $this->lat . " - 0.5 AND
                    longitude < " . $this->lng . " + 0.5 AND
                    longitude > " . $this->lng . " - 0.5 
                    AND fclass <> 'A' 
                    AND country <> '' 
                ORDER BY 
                    acos(cos(radians(" . $this->lat . "))*cos(radians(latitude))*cos(radians(longitude)-radians(" . $this->lng . "))+sin(radians(" . $this->lat . "))*sin(radians(latitude)))
                LIMIT 1;";
            $result_obj = $this->connector->query($sql)->fetch_object();
            $this->country_code = $result_obj->country;
        } else {
            $this->country_code = '';
        }
    }

    private function retrieveCountryName(string $lang) : void
    {
        if ($this->country_code) {
            $sql = "SELECT $lang AS country_intl FROM countries WHERE alpha_2 = '" . strtolower($this->country_code) . "';";
            $result_obj = $this->connector->query($sql);
            if ($result_obj->num_rows > 0) {
                $this->country_name[$lang] = $result_obj->fetch_object()->country_intl;
            } else {
                $this->country_name[$lang] = $this->country_code;
            }
        } else {
            $this->country_name[$lang] = '';
        }
    }

    private function retrieveNearbyPlace() : void
    {
        $load = sys_getloadavg()[0];
        if ($load < 6.0) {
            $result_obj = $this->connector->query(
                "SELECT name FROM geo_allcountries
                WHERE 
                    latitude < " . $this->lat . " + 0.5
                    AND latitude > " . $this->lat . " - 0.5
                    AND longitude < " . $this->lng . " + 0.5
                    AND longitude > " . $this->lng . " - 0.5 
                    AND fclass = 'P' 
                    AND fcode <> 'PPLX'
                    AND fcode <> 'PPLCH'
                    AND fcode <> 'PPLH'
                ORDER BY 
                    acos(cos(radians(" . $this->lat . "))*cos(radians(latitude))*cos(radians(longitude)-radians(" . $this->lng . "))+sin(radians(" . $this->lat . "))*sin(radians(latitude)))  / log10 (population + 50)
                LIMIT 1;"
            );
            $this->nearby_place = $result_obj->fetch_object()->name;
        } else {
            $this->nearby_place = '';
        }
    }
}