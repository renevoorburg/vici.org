<?php

namespace Vici\Model\Site\Image;

use ArrayAccess;
use IteratorAggregate;
use Countable;
use ArrayIterator;

use Vici\Model\LazyLoadTrait;

use Vici\Model\LazyLoading;

class ImageCollection implements ArrayAccess, IteratorAggregate, Countable, LazyLoading
{
    use LazyLoadTrait;

    /** @var Image[] */
    private array $images = [];


    public function __construct(array $images = [])
    {
        $this->addLoadedImages($images);
    }

    public function offsetExists($offset): bool
    {
        $this->ensureLoaded();
        return isset($this->images[$offset]);
    }

    public function offsetGet($offset): ?Image
    {
        $this->ensureLoaded();
        return $this->images[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->ensureLoaded();
        if ($value instanceof Image) {
            $this->images[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        $this->ensureLoaded();
        unset($this->images[$offset]);
    }

    public function getIterator(): \Traversable
    {
        $this->ensureLoaded();
        return new ArrayIterator($this->images);
    }

    public function count(): int
    {
        $this->ensureLoaded();
        return count($this->images);
    }
    
    /** 
     * For use in closure passed to setLazyLoader.
     */
    public function addLoadedImages(array $images): void
    {
        foreach ($images as $key => $image) {
            if ($image instanceof Image) {
                $this->images[$key] = $image;
            }
        }
    }

}
