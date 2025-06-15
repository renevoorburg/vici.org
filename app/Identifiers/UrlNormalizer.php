<?php

namespace Vici\Identifiers;

interface UrlNormalizer
{
    public static function isValidUrl(string $url): bool;

    public static function isValidTag(string $tag): bool;

    public static function isValidId(string $id): bool;

    public static function urlToTag(string $url): string;

    /* stores resulting Url */
    public function getUrlProvidingTag(string $tag, bool $useStoredValue): string;

    /* stores resulting Url */
    public function getUrlProvidingId(string $id, bool $useStoredValue): string;

    /* stores resulting Url */
    public function getUrlProvidingUrl(string $url, bool $useStoredValue): string;

    /* returns Id - variant of URI stored in DB - based on stored value of Url */
    public function getId(): string;

}