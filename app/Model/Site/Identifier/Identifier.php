<?php

namespace Vici\Model\Site\Identifier;

class Identifier
{
    public string $uri;
    public string $namespace;
    public string $prefix;

    public function getQname(): string
    {
        if (empty($this->prefix)) {
            return $this->uri;
        }
        return $this->prefix . ':' . $this->getLocalValue();
    } 

    public function getLocalValue(): string
    {
        if (empty($this->namespace)) {
            return $this->uri;
        }
        return (strpos($this->uri, $this->namespace) === 0)
            ? substr($this->uri, strlen($this->namespace))
            : $this->uri;
    }
}
