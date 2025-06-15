<?php

namespace Vici\Identifiers;

use Vici\Identifiers\Normalizers\Livius;
use Vici\Identifiers\Normalizers\Pleiades;
use Vici\Identifiers\Normalizers\Wikidata;
use Vici\Identifiers\Normalizers\Dare;
use Vici\Identifiers\Normalizers\Romaq;
use Vici\Identifiers\Normalizers\Omnesviae;
use Vici\Identifiers\Normalizers\Mithraeum;

class NormalizersIndex
{
    public static function getNormalizerKeys() : array
    {
        return array ('pleiades', 'livius', 'romaq', 'dare', 'wikidata', 'omnesviae', 'mithraeum');
    }

    public static function getIndexedNormalizer(string $key) : UrlNormalizerInterface
    {
        switch ($key) {
            case 'pleiades':
                return new Pleiades();
            case 'livius':
                return new Livius();
            case 'romaq':
                return new Romaq();
            case 'dare':
                return new Dare();
            case 'wikidata':
                return new Wikidata();
            case 'omnesviae':
                return new Omnesviae();
            case 'mithraeum':
                return new Mithraeum();
        }
        return self;
    }
}