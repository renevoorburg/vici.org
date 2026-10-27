<?php

namespace Vici\Negotiator;

abstract class Negotiator {
    protected $supportedValues = [];
    protected $headerName = '';
    protected $urlParamName = '';

    public function __construct(array $supportedValues) {
        $this->supportedValues = $supportedValues;
    }

    public function negotiate() {
        if (!empty($this->urlParamName) && !empty($_GET[$this->urlParamName])) {
            $urlParamValue = $_GET[$this->urlParamName];
            if (in_array($urlParamValue, $this->supportedValues)) {
                return $urlParamValue;
            }
        }

        $headerValue = $_SERVER[$this->headerName] ?? '';
        $values = $this->parseHeaderValue($headerValue);
        arsort($values); // Sort by quality

        foreach ($values as $value => $quality) {
            if (in_array($value, $this->supportedValues)) {
                return $value;
            }
        }

        return $this->supportedValues[0]; // No acceptable value found, first as a default
    }

    protected function parseHeaderValue($headerValue) {
        $items = explode(',', $headerValue);
        $parsed = [];
        foreach ($items as $item) {
            $parts = explode(';q=', $item);
            $value = trim($parts[0]);
            $quality = $parts[1] ?? 1;
            $parsed[$value] = (float)$quality;
        }
        return $parsed;
    }
}
