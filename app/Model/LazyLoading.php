<?php

namespace Vici\Model;

interface LazyLoading
{
    /**
     * Interface to indicate that a class supports deferred (lazy) loading of its data.
     * Allows external code to inject a loader and treat the object as lazy-loadable.
     */
    public function setLazyLoader(callable $loader): void;
}
