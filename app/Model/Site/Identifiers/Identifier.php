<?php

namespace Vici\Model\Site\Identifiers;

class Identifier
{
    public string $uri;
    public string $namespace;
    public string $prefix;

    public function getQname(): string
    {
        if (strpos($this->uri, $this->namespace) === 0) {
            $localPart = substr($this->uri, strlen($this->namespace));
        } else {
            $localPart = $this->uri;
        }
        return $this->prefix . ':' . $localPart;
    }   
}
