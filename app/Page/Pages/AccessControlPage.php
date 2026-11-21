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

        $rows = [];
        foreach (AccessControl::listBlocks() as $subnet => $ttl) {
            $rows[] = ['key' => $subnet, 'ttl' => $formatTtl($ttl)];
        }
        $this->assign('subnet_blocks', $rows);
    }
}
