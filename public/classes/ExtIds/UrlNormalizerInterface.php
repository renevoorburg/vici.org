<?php declare(strict_types=1);
/* RV 20220202 */

namespace ExtIds;

interface UrlNormalizerInterface
{
    public function setUrlByTagOrUrl(string $tagOrUrl): bool;
    public function setUrlById(string $id): bool;

    public function getId(): string;
    public function getUrl(): string;
    public function getTag(): string;
}