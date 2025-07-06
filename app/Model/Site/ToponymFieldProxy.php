<?php

namespace Vici\Model\Site;

use LogicException;
use ArrayAccess;
use Vici\Service\ReverseGeocoder;


class ToponymFieldProxy implements ArrayAccess
{
    private Toponym $toponym;
    private string $field;
    private array $valuesPerLang = [];

    public function __construct(Toponym $toponym, string $field)
    {
        $this->toponym = $toponym;
        $this->field = $field;
    }

    public function offsetExists($offset): bool
    {
        $this->ensureLoaded($offset);
        return isset($this->valuesPerLang[$offset]);
    }

    public function offsetGet($offset)
    {
        $this->ensureLoaded($offset);
        return $this->valuesPerLang[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        throw new LogicException("Niet schrijfbaar");
    }

    public function offsetUnset($offset): void
    {
        throw new LogicException("Niet unsetbaar");
    }

    private function ensureLoaded(string $lang): void
    {
        if (!array_key_exists($lang, $this->valuesPerLang)) {
            $geocoder = new ReverseGeocoder(
                $this->toponym->getLatitude(),
                $this->toponym->getLongitude()
            );
            $data = $geocoder->resolveToponym($lang);
            $this->valuesPerLang[$lang] = $data[$this->field][$lang] ?? null;
        }

    }
}