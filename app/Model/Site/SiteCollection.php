<?php

namespace Vici\Model\Site;

use ArrayAccess;
use IteratorAggregate;
use Countable;
use ArrayIterator;
use Vici\Model\LazyLoadTrait;
use Vici\Model\LazyLoading;

class SiteCollection implements ArrayAccess, IteratorAggregate, Countable, LazyLoading
{
    use LazyLoadTrait;

    /** @var Site[] */
    private array $sites = [];

    public function __construct(array $sites = [])
    {
        $this->addLoadedSites($sites);
    }

    public function offsetExists($offset): bool
    {
        $this->ensureLoaded();
        return isset($this->sites[$offset]);
    }

    public function offsetGet($offset): ?Site
    {
        $this->ensureLoaded();
        return $this->sites[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->ensureLoaded();
        if ($value instanceof Site) {
            $this->sites[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        $this->ensureLoaded();
        unset($this->sites[$offset]);
    }

    public function getIterator(): \Traversable
    {
        $this->ensureLoaded();
        return new ArrayIterator($this->sites);
    }

    public function count(): int
    {
        $this->ensureLoaded();
        return count($this->sites);
    }

    /**
     * For use in closure passed to setLazyLoader.
     */
    public function addLoadedSites(array $sites): void
    {
        foreach ($sites as $key => $site) {
            if ($site instanceof Site) {
                $this->sites[$key] = $site;
            }
        }
    }
}
