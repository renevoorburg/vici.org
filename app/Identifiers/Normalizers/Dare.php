<?php declare(strict_types=1);

namespace ExtIds\Normalizers;

class Dare extends AbstractNormalizer implements \ExtIds\UrlNormalizerInterface
{
    protected function getValidUrlPattern(): string {
        return '/^https?:\/\/(?:imperium\.ahlfeldt|dh\.gu|francia\.ahlfeldt|dare\.ht\.lu)\.se\/(?:dare\/)?(?:page\/)?(?:places)\/([1-9][0-9]*)(?:\.html)?$/i';
    }

    protected function getValidTagPattern(): string {
        return '/^dare( |:)?(?:id|place)?=\s*[1-9][0-9]*$/i';
    }

    protected function getValidIdPattern(): string {
        return '/^[1-9][0-9]*+$/i' ;
    }

    protected function getUrlToTagPatternArray(): array {
        return array ('/^https?:\/\/(?:imperium\.ahlfeldt|dh\.gu|francia\.ahlfeldt|dare\.ht\.lu)\.se\/(?:dare\/)?(?:page\/)?(?:places)\/([1-9][0-9]*)(?:\.html)?$/i', 'dare:place=$1');
    }

    protected function getTagToUrlPatternArray() : array {
        return array ('/^dare(?: |:)(?:place|id)?=\s*([1-9][0-9]*)$/i', 'http://imperium.ahlfeldt.se/places/$1');
    }

    protected function getUrlToIdPatternArray(): array {
        return array ('/^https?:\/\/(?:imperium\.ahlfeldt|dh\.gu|francia\.ahlfeldt|dare\.ht\.lu)\.se\/(?:dare\/)?(?:page\/)?(?:places)\/([1-9][0-9]*)(?:\.html)?$/i', '$1');
    }

    protected function getIdToUrlPatternArray(): array {
        return array ('/^([1-9][0-9]*)$/i', 'http://imperium.ahlfeldt.se/places/$1');
    }

}