<?php

namespace Vici\Identifiers;

interface UrlNormalizerInterface
{
    public function setUrlByTagOrUrl(string $tagOrUrl): bool;
    public function setUrlById(string $id): bool;

    public function getId(): string;
    public function getUrl(): string;
    public function getTag(): string;
}