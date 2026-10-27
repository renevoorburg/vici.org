<?php

namespace Vici\Model\Site\Line;

use Vici\Model\AbstractCollection;
use Vici\Model\LazyLoadTrait;
use Vici\Model\User\User;
use Vici\Model\License\License;

class LineCollection extends AbstractCollection
{
    use LazyLoadTrait;

    public User $uploader;
    public License $license;

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
