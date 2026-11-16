<?php

namespace Vici\Identifiers;

class IdentifierValidator
{
    /**
     * Returns the normalized identifier URL if the value matches a supported
     * pattern (i.e. it would be displayed), null otherwise.
     */
    public static function normalize(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        foreach (NormalizersIndex::getNormalizerKeys() as $key) {
            $normalizer = NormalizersIndex::getIndexedNormalizer($key);
            if ($normalizer->setUrlByTagOrUrl($value) && $normalizer->getUrl() !== '') {
                return $normalizer->getUrl();
            }
        }
        return null;
    }
}
