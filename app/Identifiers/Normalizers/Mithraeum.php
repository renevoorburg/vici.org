<?php declare(strict_types=1);

namespace ExtIds\Normalizers;

class Mithraeum extends AbstractNormalizer implements \ExtIds\UrlNormalizerInterface
{
    protected function getValidUrlPattern(): string {
        return '/^https?:\/\/(?:www\.)?mithraeum\.eu\/(?:monument|person|book)\/([0-9]+)$/i';
    }

    protected function getValidTagPattern(): string {
        return '/^mithraeum:(?:monument|person|book)\s*=\s*[0-9]+$/i';
    }

    protected function getValidIdPattern(): string {
        return '/^(?:monument|person|book)\s*=\s*[0-9]+$/i' ;
    }

    protected function getUrlToTagPatternArray(): array {
        return array ('/^https?:\/\/(?:www\.)?mithraeum\.eu\/(monument|person|book)\/([0-9]+)$/i', 'mithraeum:$1=$2');
    }

    protected function getTagToUrlPatternArray() : array {
        return array ('/^mithraeum:(monument|person|book)\s*=\s*([0-9]+)$/i', 'https://www.mithraeum.eu/$1/$2');
    }

    protected function getUrlToIdPatternArray(): array
    {
        return array ('/^https?:\/\/(?:www\.)?mithraeum\.eu\/(monument|person|book)\/([0-9]+)$/i', '$1=$2' );
    }

    protected function getIdToUrlPatternArray(): array {
        return array ('/^(monument|person|book)\s*=\s*([0-9]+)$/i', 'https://www.mithraeum.eu/$1/$2');
    }

}