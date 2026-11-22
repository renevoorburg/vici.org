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

        // Merge adjacent blocked /24s into the smallest set of CIDR
        // blocks: a botnet sweeping 131.222.136-183 shows as a handful
        // of supernets instead of ~50 rows. IPv6 keys pass through.
        $v4ranges = [];
        $v6 = [];
        foreach (AccessControl::listBlocks() as $subnet => $ttl) {
            if (str_starts_with($subnet, 'v4:')) {
                $start = ip2long(substr($subnet, 3) . '.0');
                $v4ranges[$start] = ['end' => $start + 255, 'ttl' => $ttl];
            } else {
                $v6[] = ['key' => $formatSubnet($subnet), 'ttl' => $ttl];
            }
        }
        ksort($v4ranges);

        // merge adjacent/overlapping ranges, then split each into
        // aligned CIDR blocks (start determines the largest legal size)
        $merged = [];
        foreach ($v4ranges as $start => $r) {
            $last = end($merged);
            if ($last !== false && $start <= $last['end'] + 1) {
                $merged[key($merged)]['end'] = max($last['end'], $r['end']);
                $merged[key($merged)]['ttl'] = max($last['ttl'], $r['ttl']);
                $merged[key($merged)]['count'] += 1;
            } else {
                $merged[$start] = ['end' => $r['end'], 'ttl' => $r['ttl'], 'count' => 1];
            }
        }

        $rows = [];
        foreach ($merged as $start => $r) {
            while ($start <= $r['end']) {
                // largest block aligned at $start that fits the range:
                // capped by the trailing zeros of $start (alignment)
                // and by the remaining length
                $align = $start === 0 ? 32 : (int)log($start & -$start, 2);
                $fit = (int)floor(log($r['end'] - $start + 1, 2));
                $blockBits = min($align, $fit);
                $count = 1 << ($blockBits - 8); // /24s covered
                $rows[] = [
                    'key' => long2ip($start) . '/' . (32 - $blockBits),
                    'ttl' => $formatTtl($r['ttl']),
                    'count' => $count > 1 ? $count : null,
                ];
                $start += 1 << $blockBits;
            }
        }
        foreach ($v6 as $row) {
            $row['ttl'] = $formatTtl($row['ttl']);
            $row['count'] = null;
            $rows[] = $row;
        }
        $this->assign('subnet_blocks', $rows);
    }
}
