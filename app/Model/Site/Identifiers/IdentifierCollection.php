<?php

namespace Vici\Model\Site\Identifiers;

use ArrayAccess;
use IteratorAggregate;
use Countable;
use ArrayIterator;
use Vici\Model\LazyLoadTrait;
use Vici\Model\LazyLoading;

use Vici\Model\AbstractCollection;

class IdentifierCollection extends AbstractCollection
{
    public function offsetGet($offset): ?Identifier
    {
        /** @var Identifier|null */
        return parent::offsetGet($offset);
    }

    protected function isValidItem($item): bool
    {
        return $item instanceof Identifier;
    }
    
    use LazyLoadTrait;

}