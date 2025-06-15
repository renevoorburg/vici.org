<?php declare(strict_types=1);

namespace ExtIds\Normalizers;

class Pleiades extends AbstractNormalizer implements \ExtIds\UrlNormalizerInterface
{
    protected function getValidUrlPattern(): string {
        return '/^https?:\/\/pleiades\.stoa\.org\/places\/([1-9][0-9]*)$/';
    }

    protected function getValidTagPattern(): string {
        return '/^pleiades:place=[1-9][0-9]*$/i';
    }

    protected function getValidIdPattern(): string {
        return '/^[1-9][0-9]*$/' ;
    }

    protected function getUrlToTagPatternArray(): array {
        return array ('/^https?:\/\/pleiades\.stoa\.org\/places\/([1-9][0-9]*)$/', 'pleiades:place=$1');
    }

    protected function getTagToUrlPatternArray() : array {
        return array ('/^pleiades:place\s*=\s*([1-9][0-9]*)$/i', 'https://pleiades.stoa.org/places/$1');
    }

    protected function getUrlToIdPatternArray(): array {
        return array ('/^https?:\/\/pleiades\.stoa\.org\/places\/([1-9][0-9]*)$/', '$1');
    }

    protected function getIdToUrlPatternArray(): array {
        return array ('/^([1-9][0-9]*)$/i', 'https://pleiades.stoa.org/places/$1');
    }

}