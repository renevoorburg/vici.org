<?php

namespace Vici\Model;

use ArrayAccess;
use IteratorAggregate;
use Countable;
use ArrayIterator;

abstract class AbstractCollection implements ArrayAccess, IteratorAggregate, Countable, LazyLoading
{
    use LazyLoadTrait;

    /** @var array */
    protected $items = [];

    public function __construct(array $items = [])
    {
        $this->addLoadedItems($items);
    }

    public function offsetExists($offset): bool
    {
        $this->ensureLoaded();
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        $this->ensureLoaded();
        return $this->items[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->ensureLoaded();
        if ($this->isValidItem($value)) {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        $this->ensureLoaded();
        unset($this->items[$offset]);
    }

    public function getIterator(): \Traversable
    {
        $this->ensureLoaded();
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        $this->ensureLoaded();
        return count($this->items);
    }

    public function addLoadedItems(array $items): void
    {
        foreach ($items as $key => $item) {
            if ($this->isValidItem($item)) {
                $this->items[$key] = $item;
            }
        }
    }

    abstract protected function isValidItem($item): bool;
}
