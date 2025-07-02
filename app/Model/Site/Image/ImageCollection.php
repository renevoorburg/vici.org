<?php

namespace Vici\Model\Site\Image;

use ArrayAccess;
use IteratorAggregate;
use Countable;
use ArrayIterator;

class ImageCollection implements ArrayAccess, IteratorAggregate, Countable
{
    /** @var Image[] */
    private array $images = [];

    public function __construct(array $images = [])
    {
        foreach ($images as $key => $image) {
            if ($image instanceof Image) {
                $this->images[$key] = $image;
            }
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->images[$offset]);
    }

    public function offsetGet($offset): ?Image
    {
        return $this->images[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if ($value instanceof Image) {
            $this->images[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->images[$offset]);
    }

    public function getIterator(): \Traversable
    {
        return new ArrayIterator($this->images);
    }

    public function count(): int
    {
        return count($this->images);
    }

    /**
     * Optionally: add more convenience methods here, zoals getFirstImage(), filterByLicense(), etc.
     */
}
