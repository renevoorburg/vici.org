<?php declare(strict_types=1);

namespace ExtIds\Normalizers;

class Wikidata extends AbstractNormalizer implements \ExtIds\UrlNormalizerInterface
{
    protected function getValidUrlPattern(): string {
        return '/^https?:\/\/(?:www\.)?wikidata\.org\/(?:entity|wiki)\/(Q[0-9]+)$/i';
    }

    protected function getValidTagPattern(): string {
        return '/^wikidata:?(?:entity)?=q[0-9]*$/i';
    }

    protected function getValidIdPattern(): string {
        return '/^q[0-9]*$/i' ;
    }

    protected function getUrlToTagPatternArray(): array {
        return array (
            array ('/^https?:\/\/(?:www\.)?wikidata\.org\/(?:entity|wiki)\/Q([0-9]+)$/i', 'wikidata:entity=Q$1')
        );
    }

    protected function getTagToUrlPatternArray() : array {
        return array ('/^wikidata:?(?:entity)?=q([0-9]+)$/i', 'https://wikidata.org/entity/Q$1');
    }

    protected function getUrlToIdPatternArray(): array {
        return array ('/^https?:\/\/(?:www\.)?wikidata\.org\/(?:entity|wiki)\/Q([0-9]+)$/i', 'Q$1');
    }

    protected function getIdToUrlPatternArray(): array {
        return array ('/^Q([0-9]+)$/i', 'https://wikidata.org/entity/Q$1',);
    }

}