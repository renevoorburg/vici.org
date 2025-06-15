<?php declare(strict_types=1);

namespace ExtIds\Normalizers;

class Romaq extends AbstractNormalizer implements \ExtIds\UrlNormalizerInterface
{
    protected function getValidUrlPattern(): string {
        return '/^https?:\/\/(?:www\.)?romaq\.org\/the-project\/aqueducts\/(?:article\/)?([0-9]+)(?:-.*\.html)?$/';
    }

    protected function getValidTagPattern(): string {
        return '/^romaq( |:)?(?:aqueduct)?\s*=\s*[0-9]+$/';
    }

    protected function getValidIdPattern(): string {
        return '/^[0-9]+$/' ;
    }

    protected function getUrlToTagPatternArray(): array {
        return array ('/^https?:\/\/(?:www\.)romaq\.org\/the-project\/aqueducts\/(?:article\/)?([0-9]+)(?:-.*\.html)?$/i', 'romaq:aqueduct=$1');
    }

    protected function getTagToUrlPatternArray() : array {
        return array ('/^romaq:aqueduct\s*=\s*([0-9]+)$/i', 'https://www.romaq.org/the-project/aqueducts/$1');
    }

    protected function getUrlToIdPatternArray(): array {
        return array ('/^https?:\/\/(?:www\.)?romaq\.org\/the-project\/aqueducts\/(?:article\/)?([0-9]+)(?:-.*\.html)?$/', '$1');
    }

    protected function getIdToUrlPatternArray(): array {
        return array ('/^([0-9]+)$/i', 'https://www.romaq.org/the-project/aqueducts/$1');
    }

}