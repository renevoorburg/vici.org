<?php declare(strict_types=1);
/* RV 20220202 */

namespace ExtIds;

class ExternalIdentifiers
{
    protected array $normalizerArr;
    protected array $otherUrlsArr = []; // URLs not known by the normalizers

    // to be called from either static 'with_' method:
    public function __construct(string $perspective = '') {
        if (empty($perspective)) {
            foreach (NormalizersIndex::getNormalizerKeys() as $key) {
                $this->normalizerArr[$key] = NormalizersIndex::getIndexedNormalizer($key);
            }
        } elseif (in_array($perspective, NormalizersIndex::getNormalizerKeys())) {
            $this->normalizerArr[$perspective] = NormalizersIndex::getIndexedNormalizer($perspective);
        }
    }

    /* $spannedURLs may contain tags in various forms, eg wikidata:Q123 , wikidata:entity=Q123,
    * urls uncanonical forms, all insides separate <span></span> elements.
    * Urls that have separate Ids in the database may or may not be included in $spannedURLs.
    * Junk like spaces might be there too.
    * Implicitly provided urls using Ids will take precedence over urls provided in $spannedUrls.
    */
    public static function withDbParams (
        string $spannedUrlsAndTags,
        string $pleiadesId = '',
        string $liviusId = '',
        string $romaqId = '',
        string $dareId = ''
    ) : self
    {
        $instance = new self();

        $spanArr = mb_split("</span>", mb_ereg_replace('<span>', '', $spannedUrlsAndTags));
        $instance->runNormalizers($spanArr);

        // explicitly provided Ids overwrite <span>ned Ids:
        if (!empty($pleiadesId)) $instance->normalizerArr['pleiades']->setUrlById($pleiadesId);
        if (!empty($liviusId)) $instance->normalizerArr['livius']->setUrlById($liviusId);
        if (!empty($dareId)) $instance->normalizerArr['dare']->setUrlById($dareId);
        if (!empty($romaqId)) $instance->normalizerArr['romaq']->setUrlById($romaqId);
        $instance->otherUrlsArr = array_unique($instance->otherUrlsArr);
        return $instance;
    }

    public static function withUrlsAndTagsArray (array $urlsAndTagsArr, string $perspective = '') : self
    {
        $instance = new self($perspective);
        $instance->runNormalizers($urlsAndTagsArr);
        return $instance;
    }

    public static function urlsArraySpanned (array $urlsArr ) : string
    {
        if(count($urlsArr) > 0) {
            return  '<span>' . implode('</span><span>', $urlsArr) . '</span>';
        }
        return '';
    }

    public function setById(string $key, $value) : void
    {
        $this->normalizerArr[$key]->setUrlById($value);
    }

    public function getOtherUrlsArray() : array
    {
        return $this->otherUrlsArr;
    }

    public function getAllUrlsArray() : array
    {
        $idUrlsArr = [];
        foreach ($this->normalizerArr as $normalizer) {
            if (!empty($url = $normalizer->getUrl())) {
                $idUrlsArr[] = $url;
            }
        }
        return array_merge($idUrlsArr, $this->otherUrlsArr);
    }

    public function getAllUrlsSpanned() : string
    {
        return $this->urlsArraySpanned($this->getAllUrlsArray());
    }

    public function getIdentifierKeys(): array
    {
        return  array_keys($this->normalizerArr);
    }

    public function getId(string $key) : string
    {
        if(array_key_exists($key, $this->normalizerArr)) {
            return $this->normalizerArr[$key]->getId();
        }
        return '';
    }

    public function getUrl(string $key) : string
    {
        if(array_key_exists($key, $this->normalizerArr)) {
            return $this->normalizerArr[$key]->getUrl();
        }
        return '';
    }

    public function getTag(string $key) : string
    {
        if(array_key_exists($key, $this->normalizerArr)) {
            return $this->normalizerArr[$key]->getTag();
        }
        return '';
    }

    private function extractKnownId(string $value) : bool
    {
        foreach ($this->normalizerArr as $object) {
            if ($object->setUrlByTagOrUrl($value)) {
                return true;
            }
        }
        return false;
    }

    private function runNormalizers(array $urlsAndTags) : void
    {
        foreach ($urlsAndTags as &$value) {
            $value = trim($value);
            if (!$this->extractKnownId($value)) {
                if (!empty($value)) {
                    $this->otherUrlsArr[] = $value;
                }
            }
        }
    }


}