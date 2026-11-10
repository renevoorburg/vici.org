<?php

namespace Vici\Model\User;

class UserBio
{
    public string $bio = '';
    /** @var array<int, array{label: string, url: string}> */
    public array $links = [];
    public bool $public = true;
    public bool $contact = false;

    /** Label+url pairs where the url is a valid http(s) address. */
    public function getSafeLinks(): array
    {
        return array_values(array_filter($this->links, fn($l) =>
            ($l['label'] ?? '') !== '' && preg_match('#^https?://#i', $l['url'] ?? '')));
    }
}
