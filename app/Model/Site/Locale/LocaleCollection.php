<?php

namespace Vici\Model\Site\Locale;

class LocaleCollection implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /** @var Locale[] */
    private array $locales = [];

    public function __construct(array $locales = [], string $defaultTitle = '', string $defaultSummary = '')
    {
        foreach ($locales as $key => $locale) {
            if ($locale instanceof Locale) {
                if (empty($locale->title)) {
                    $locale->title = $defaultTitle;
                }
                if (empty($locale->summary)) {
                    $locale->summary = $defaultSummary;
                }
                $this->locales[$key] = $locale;
            }
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->locales[$offset]);
    }

    public function offsetGet($offset): ?Locale
    {
        return $this->locales[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if ($value instanceof Locale) {
            $this->locales[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->locales[$offset]);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->locales);
    }

    public function count(): int
    {
        return count($this->locales);
    }

    /**
     * Geeft de key (taalcode) van de eerste locale terug
     */
    public function getFirstLocaleKey(): ?string
    {
        return array_key_first($this->locales);
    }

    /**
     * Geeft de SiteLocale voor een gegeven taalcode, of null
     */
    public function getByLanguage(string $language): ?Locale
    {
        return $this->locales[$language] ?? null;
    }

    /**
     * Geeft de eerste niet-lege titel terug
     */
    public function getFirstTitle(): string
    {
        foreach ($this->locales as $locale) {
            if (!empty($locale->title)) {
                return $locale->title;
            }
        }
        return '';
    }

    public function preferredLocale(string $language): string
    {
        if ($this->offsetExists($language)) {
            return $language;
        } else if ($this->offsetExists('en')) {
            return 'en';
        } else {
            return $this->getFirstLocaleKey();
        }
    }

    // Voeg hier meer functionaliteit toe indien gewenst
}
