<?php

namespace ExtIds\Normalizers;

abstract class AbstractNormalizer implements \ExtIds\UrlNormalizerInterface
{
    protected string $url = '';

    abstract protected function getValidUrlPattern(): string;
    abstract protected function getValidTagPattern(): string;
    abstract protected function getValidIdPattern(): string;
    abstract protected function getUrlToTagPatternArray(): array;
    abstract protected function getUrlToIdPatternArray(): array;
    abstract protected function getTagToUrlPatternArray(): array;
    abstract protected function getIdToUrlPatternArray(): array;

    private static function transformer(array $normalizerArr, string $source) : string
    {
        if (count($normalizerArr) == count($normalizerArr, COUNT_RECURSIVE)) {
            // one dimensional pattern array:
            $ret = preg_replace($normalizerArr[0], $normalizerArr[1], $source, -1, $count);
            return ($count > 0) ? $ret : '';
        } else  {
            // assume two dimensional pattern array:
            foreach ($normalizerArr as $rule) {
                $ret = preg_replace($rule[0], $rule[1], $source, -1, $count);
                if ($count > 0) {
                    return $ret;
                }
            }
            return '';
        }
    }

    private function isValidURL(string $url) : bool
    {
        return (preg_match($this->getValidUrlPattern(), $url) > 0);
    }

    private function isValidTag(string $tag) : bool
    {
        return (preg_match($this->getValidTagPattern(), $tag) > 0);
    }

    private function isValidId(string $id): bool
    {
        return (preg_match($this->getValidIdPattern(), $id) > 0);
    }

    private function urlToTag(string $url) : string
    {
        $tag = $this->transformer($this->getUrlToTagPatternArray(), $url);
        return ($this->isValidTag($tag)) ? $tag : '';
    }

    private function tagToUrl(string $tag) : string
    {
        $url = $this->transformer($this->getTagToUrlPatternArray(), $tag);
        return ($this->isValidUrl($url)) ? $url : '';
    }

    public function idToUrl(string $id) : string
    {
        if ($this->isValidId($id)) {
            $url = $this->transformer($this->getIdToUrlPatternArray(), $id);
            return $url;
        }
        return '';
    }

    //  sets $this->url
    public function setUrlByTagOrUrl(string $tagOrUrl) : bool
    {
        if ($this->isValidTag($tagOrUrl)) {
            $this->url = $this->tagToUrl($tagOrUrl);
            return true;
        }  elseif ($this->isValidURL($tagOrUrl)) {
            // normalize Url by converting to tag and calling setUrlByTagOrUrl again:
            return ($this->setUrlByTagOrUrl($this->urlToTag($tagOrUrl)));
        }
        return false;
    }

    public function setUrlById(string $id): bool
    {
        $url = $this->idToUrl($id);
        if ($this->isValidURL($url)) {
            $this->url = $url;
            return true;
        }
        return false;
    }

    public function getId() : string
    {
        return $this->transformer($this->getUrlToIdPatternArray(), $this->url);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getTag(): string
    {
        return $this->urlToTag($this->url);
    }

}