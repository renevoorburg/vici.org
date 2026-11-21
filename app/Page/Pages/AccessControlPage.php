<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;
use Vici\Security\AccessControl;

class AccessControlPage extends PageRenderer
{
    private string $template = 'accesscontrol.tpl';

    public function __construct(Session $session)
    {
        $user = $session->hasUser() ? $session->getUser() : null;
        if ($user === null || !$user->isAdmin()) {
            AccessControl::denyAccess();
        }

        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);

        $formatTtl = function (int $seconds): string {
            if ($seconds <= 0) {
                return '—';
            }
            $days = intdiv($seconds, 86400);
            $hours = intdiv($seconds % 86400, 3600);
            $minutes = intdiv($seconds % 3600, 60);
            if ($days > 0) {
                return $days . 'd ' . $hours . 'h';
            }
            if ($hours > 0) {
                return $hours . 'h ' . $minutes . 'm';
            }
            return $minutes . 'm ' . ($seconds % 60) . 's';
        };

        // subnet keys are stored as 'v4:a.b.c' or 'v6:<16 hex chars>';
        // display them in standard CIDR notation
        $formatSubnet = function (string $key): string {
            if (str_starts_with($key, 'v4:')) {
                return substr($key, 3) . '.0/24';
            }
            // v6: first 64 bits — pad to a full address, then let
            // inet_ntop compress it for display
            $full = substr($key, 3) . str_repeat('0', 16);
            $groups = implode(':', str_split($full, 4));
            $packed = @inet_pton($groups);
            return ($packed !== false ? inet_ntop($packed) : $key) . '/64';
        };

        $rows = [];
        foreach (AccessControl::listBlocks() as $subnet => $ttl) {
            $rows[] = ['key' => $formatSubnet($subnet), 'ttl' => $formatTtl($ttl)];
        }
        $this->assign('subnet_blocks', $rows);
    }
}
