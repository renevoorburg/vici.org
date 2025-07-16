<?php

namespace Vici\Model\Site\Line;

use Vici\Model\AbstractCollection;
use Vici\Model\LazyLoadTrait;

class LineCollection extends AbstractCollection
{
    use LazyLoadTrait;

    public function offsetGet($offset): ?Line
    {
        /** @var Line|null */
        return parent::offsetGet($offset);
    }

    protected function isValidItem($item): bool
    {
        return $item instanceof Line;
    }
}
