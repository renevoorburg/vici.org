<?php

namespace Vici\Model;

trait LazyLoadTrait
{
    /** @var callable|null */
    protected $lazyLoader = null;
    /** @var bool */
    protected $loaded = false;

    public function setLazyLoader(callable $loader): void
    {
        $this->lazyLoader = $loader;
        $this->loaded = false;
    }

    protected function ensureLoaded(): void
    {
        if (!$this->loaded && $this->lazyLoader) {
            ($this->lazyLoader)($this);
            $this->loaded = true;
        }
    }
}
