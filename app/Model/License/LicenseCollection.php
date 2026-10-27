<?php

namespace Vici\Model\License;

use ArrayAccess;
use IteratorAggregate;
use Countable;
use ArrayIterator;

class LicenseCollection implements ArrayAccess, IteratorAggregate, Countable
{
    /** @var License[] */
    private array $licenses = [];

    public function __construct(array $licenses = [])
    {
        foreach ($licenses as $key => $license) {
            if ($license instanceof License) {
                $this->licenses[$key] = $license;
            }
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->licenses[$offset]);
    }

    public function offsetGet($offset): ?License
    {
        return $this->licenses[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if ($value instanceof License) {
            $this->licenses[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->licenses[$offset]);
    }

    public function getIterator(): \Traversable
    {
        return new ArrayIterator($this->licenses);
    }

    public function count(): int
    {
        return count($this->licenses);
    }
}
