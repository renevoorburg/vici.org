<?php

namespace ExtIds;

use ExtIds\Normalizers\Livius;
use ExtIds\Normalizers\Pleiades;
use ExtIds\Normalizers\Wikidata;
use ExtIds\Normalizers\Dare;
use ExtIds\Normalizers\Romaq;
use ExtIds\Normalizers\Omnesviae;
use ExtIds\Normalizers\Mithraeum;

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