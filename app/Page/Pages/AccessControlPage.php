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

        $blocks = AccessControl::listBlocks();

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

        $ttlRows = function (array $map) use ($formatTtl) {
            $rows = [];
            foreach ($map as $key => $ttl) {
                $rows[] = ['key' => $key, 'ttl' => $formatTtl($ttl)];
            }
            return $rows;
        };

        $countRows = function (array $map) {
            $rows = [];
            foreach ($map as $key => $count) {
                $rows[] = ['key' => $key, 'count' => $count];
            }
            return $rows;
        };

        $this->assign('ip_blocks', $ttlRows($blocks['ips']));
        $this->assign('subnet_blocks', $ttlRows($blocks['subnets']));
        $this->assign('strikes', $countRows($blocks['strikes']));
        $this->assign('trap_hits', $countRows($blocks['trap_hits']));
        $this->assign('subnet_hits', $countRows($blocks['subnet_hits']));
    }
}
