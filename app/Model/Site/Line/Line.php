<?php

namespace Vici\Model\Site\Line;

class Line
{
    public int $id;
    public ?string $kind = null;
    public ?string $owner = null;
    public ?string $attribution = null;
    public ?int $uploaderId = null;
    public ?int $licenseId = null;
    public array $coordinates = [];
    private string $coordinatesAsKML = '';

    public function getCoordinatesAsKML(): string
    {
        if (empty($this->coordinatesAsKML)) {  
            foreach ($this->coordinates as $point) {
                $this->coordinatesAsKML .= $point[1].",".$point[0].",0 ";
            }
        }
        return $this->coordinatesAsKML;
    }


}
