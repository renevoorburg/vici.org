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
                $this->setDefaults($locale, $defaultTitle, $defaultSummary);
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

    public function getFirstLocaleKey(): ?string
    {
        return array_key_first($this->locales);
    }

    public function getByLanguage(string $language): ?Locale
    {
        return $this->locales[$language] ?? null;
    }

    public function getFirstTitle(): string
    {
        foreach ($this->locales as $locale) {
            if (!empty($locale->title)) {
                return $locale->title;
            }
        }
        return '';
    }

    public function preferredLocaleLanguage(string $language): string
    {
        if ($this->offsetExists($language) && $this->locales[$language]->description) {
            return $language;
        } elseif ($this->offsetExists('en') && $this->locales['en']->description) {
            return 'en';
        } else {
            $key = $this->getFirstLocaleWithDescriptionKey();
            if ($key !== null) {
                return $key;
            }
            $fallbackKey = $this->getFirstLocaleKey();
            return $fallbackKey !== null ? $fallbackKey : '';

        }
    }

    public function getFirstLocaleWithDescriptionKey(): ?string
    {
        foreach ($this->locales as $key => $locale) {
            if (!empty($locale->description)) {
                return $key;
            }
        }
        return null;
    }

    private function setDefaults(Locale $locale, string $defaultTitle, string $defaultSummary): void
    {
        if (empty($locale->title)) {
            $locale->title = $defaultTitle;
        }
        if (empty($locale->summary)) {
            $locale->summary = $defaultSummary;
        }
    }
}

