<?php

namespace Vici\Model\Site\Image;

use ArrayAccess;
use IteratorAggregate;
use Countable;
use ArrayIterator;

use Vici\Model\LazyLoadTrait;
use Vici\Model\LazyLoading;

use Vici\Model\AbstractCollection;

class ImageCollection extends AbstractCollection
{
    public function offsetGet($offset): ?Image
    {
        /** @var Image|null */
        return parent::offsetGet($offset);
    }

    protected function isValidItem($item): bool
    {
        return $item instanceof Image;
    }
    use LazyLoadTrait;

}
