<?php declare(strict_types=1);

namespace ExtIds\Normalizers;

use ExtIds\UrlNormalizer;

class AnyUrl implements \ExtIds\UrlNormalizer
{
    private string $url = '';

    public static function isValidURL(string $url) : bool
    {
        return (preg_match('/^https?:\/\/(www\.)?.+\.[a-z]+\/.*$/i', $url) > 0);
    }

    public static function isValidTag(string $tag) : bool
    {
        return (self::isValidURL($tag));
    }

    public static function isValidId(string $id): bool
    {
        return (preg_match('/^.*/i', $id) > 0);
    }

    public static function urlToTag(string $url) : string
    {
        // the order in this array DOES matter:
        if (self::isValidURL($url)) {
            return $url;
        } else {
            return '';
        }
    }

    //  sets $this->url
    public function getUrlProvidingTag(string $tag, bool $useStoredValue = false) : string
    {
        if ($useStoredValue && !empty($this->url)) {
            return $this->url;
        }
        if (!self::isValidTag($tag)) {
            return '';
        }
        $this->url = $tag;
        return $this->url;
    }

    // Ids are is meaningless in the context of just any Url !!!
    public function getUrlProvidingId(string $id, bool $useStoredValue = false): string
    {
        return '';
    }

    // sets $this->url (implicitly)
    public function getUrlProvidingUrl(string $url, bool $useStoredValue = false): string
    {
        return $this->getUrlProvidingTag(self::urlToTag($url), $useStoredValue);
    }

    // Ids are is meaningless in the context of just any Url !!!
    public function getId() : string
    {
        return '' ;
    }


}