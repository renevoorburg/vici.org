<?php

namespace Vici\Model\Site;

use ArrayAccess;
use IteratorAggregate;
use Countable;
use ArrayIterator;
use Vici\Model\LazyLoadTrait;
use Vici\Model\LazyLoading;

use Vici\Model\AbstractCollection;

class SiteCollection extends AbstractCollection
{
    public function offsetGet($offset): ?Site
    {
        /** @var Site|null */
        return parent::offsetGet($offset);
    }

    protected function isValidItem($item): bool
    {
        return $item instanceof Site;
    }
    use LazyLoadTrait;

}
