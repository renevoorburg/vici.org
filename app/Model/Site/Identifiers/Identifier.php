<?php

namespace Vici\Model\Site\Identifiers;

class Identifier
{
    public string $uri;
    public string $namespace;
    public string $prefix;

    public function getQname(): string
    {
        return $this->prefix . ':' . $this->getLocalValue();
    } 

    public function getLocalValue(): string
    {
        return (strpos($this->uri, $this->namespace) === 0)
            ? substr($this->uri, strlen($this->namespace))
            : $this->uri;
    }
}
