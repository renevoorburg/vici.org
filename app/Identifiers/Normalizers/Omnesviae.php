<?php declare(strict_types=1);

namespace Vici\Identifiers\Normalizers;

use Vici\Identifiers\UrlNormalizerInterface;
use Vici\Identifiers\Normalizers\AbstractNormalizer;

class Omnesviae extends AbstractNormalizer implements UrlNormalizerInterface
{

    public function getPrefix(): string
    {
        return 'omnesviae';
    }

    public function getNamespace(): string
    {
        return 'https://omnesviae.org/#';
    }

    protected function getValidUrlPattern(): string {
        return '/^https?:\/\/(?:www\.)?omnesviae\.org\/#((?:TP|OV)Place[0-9]+)$/i';
    }

    protected function getValidTagPattern(): string
    {
        //omnesviae:tpplace=990
        //tp:place=785
        //omnesviae:tpplace=785
        //OVPlace76
        //omnesviae:place=TPPlace785
        //omnesviae:id=TPPlace785 <= canonical

        return '/^(omnesviae|tp|OVPlace):?(place|tpplace|id)?\s*=?\s*(TPPlace|OVPlace)?[0-9]+$/';
    }

    protected function getValidIdPattern(): string {
        return '/^(?:TP|OV)Place[0-9]+/' ;
    }

    protected function getUrlToTagPatternArray(): array {
        return array ('/^https?:\/\/(?:www\.)?omnesviae\.org\/#((?:TP|OV)Place[0-9]+)$/i', 'omnesviae:id=$1');
    }

    protected function getTagToUrlPatternArray() : array {
        return array (
            array ('/^((?:TP|OV)Place[0-9]+)$/', $this->getNamespace() . '$1'),
            array ('/^(?:omnesviae|tp|OVPlace):?(?:place|tpplace|id)?\s*=?\s*((?:TPPlace|OVPlace)[0-9]+)$/', $this->getNamespace() . '$1'),
            array ('/^(?:omnesviae|tp|OVPlace):?(?:place|tpplace|id)?\s*=?\s*([0-9]+)$/', $this->getNamespace() . 'TPPlace$1')
        );
    }

    protected function getUrlToIdPatternArray(): array {
        return array ('/^https?:\/\/(?:www\.)?omnesviae\.org\/#((?:TP|OV)Place[0-9]+)$/i', '$1');
    }

    protected function getIdToUrlPatternArray(): array {
        return array ('/^((?:TPPlace|OVPlace)[0-9]+)$/i', $this->getNamespace() . '$1');
    }

}