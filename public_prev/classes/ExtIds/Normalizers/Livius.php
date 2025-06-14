<?php declare(strict_types=1);
/* RV 20220202 */

namespace ExtIds\Normalizers;

class Livius extends AbstractNormalizer implements \ExtIds\UrlNormalizerInterface
{
    protected function getValidUrlPattern(): string {
        return '/^https?:\/\/(?:www\.)?livius\.org\/[a-zA-Z]/i';
    }

    protected function getValidTagPattern(): string {
        return '/^livius:(museum|place|battle|people|religion|source-content|source-about)=(.*)$/i';
    }

    protected function getValidIdPattern(): string {
        return '/^(museum|place|battle|people|religion|source-content|source-about)=(.*)$/i';
    }

    protected function getUrlToTagPatternArray(): array {
        return array(
            array('/^https?:\/\/(?:www\.)?livius\.org\/museum\/([^\/]*)\/?$/', 'livius:museum=$1'),
            array('/^https?:\/\/(?:www\.)?livius\.org\/[^\/]+\/([^\/]+)\/([^\/]*)\/?$/', 'livius:$1=$2'),
            array('/^https?:\/\/(?:www\.)?livius\.org\/[^\/]+\/([^\/]+)\/([^\/]*)\/([^\/]*)\/?$/', 'livius:$1=$2/$3'),
            array('/^https?:\/\/(?:www\.)?livius\.org\/[^\/]+\/([^\/]+)\/([^\/]*)\/([^\/]*)\/([^\/]*)\/?$/', 'livius:$1=$2/$3/$4')
        );
    }

    protected function getTagToUrlPatternArray() : array
    {
        return array (
            array('/^livius:museum=([^\/]*)\/?$/i', 'https://livius.org/museum/$1'),
            array('/^livius:(place|battle|people|religion|source-content|source-about)=([^\/]+)\/?$/', 'https://livius.org/articles/$1/$2'),
            array('/^livius:(place|battle|people|religion|source-content|source-about)=([^\/]+)\/([^\/]+)\/?$/', 'https://livius.org/articles/$1/$2/$3'),
            array('/^livius:(place|battle|people|religion|source-content|source-about)=([^\/]+)\/([^\/]+)\/([^\/]+)\/?$/', 'https://livius.org/articles/$1/$2/$3/$4')
        );
    }

    protected function getUrlToIdPatternArray(): array {
        return array(
            array('/^https?:\/\/(?:www\.)?livius\.org\/museum\/([^\/]*)\/?$/', 'museum=$1'),
            array('/^https?:\/\/(?:www\.)?livius\.org\/[^\/]+\/([^\/]+)\/([^\/]*)\/?$/', '$1=$2'),
            array('/^https?:\/\/(?:www\.)?livius\.org\/[^\/]+\/([^\/]+)\/([^\/]*)\/([^\/]*)\/?$/', '$1=$2/$3'),
            array('/^https?:\/\/(?:www\.)?livius\.org\/[^\/]+\/([^\/]+)\/([^\/]*)\/([^\/]*)\/([^\/]*)\/?$/', '$1=$2/$3/$4')
        );
    }

    protected function getIdToUrlPatternArray(): array {
        return array (
            array('/^museum=([^\/]*)\/?$/i', 'https://livius.org/museum/$1'),
            array('/^(place|battle|people|religion|source-content|source-about)=([^\/]+)\/?$/', 'https://livius.org/articles/$1/$2'),
            array('/^(place|battle|people|religion|source-content|source-about)=([^\/]+)\/([^\/]+)\/?$/', 'https://livius.org/articles/$1/$2/$3'),
            array('/^(place|battle|people|religion|source-content|source-about)=([^\/]+)\/([^\/]+)\/([^\/]+)\/?$/', 'https://livius.org/articles/$1/$2/$3/$4')
        );
    }

}