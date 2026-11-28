<?php
/**
 * Rewrite legacy /vici/{name} links in ptexts.ptxt_full to /vici/{id}.
 * Name-based URLs are no longer routed; only numeric ids are.
 *
 * Only slugs resolving to exactly one point are rewritten. Ambiguous or
 * unresolvable slugs are left untouched and reported for manual review.
 *
 * Dry run:  docker exec vici-novo-php php bin/fix_vici_name_links.php
 * Apply:    docker exec vici-novo-php php bin/fix_vici_name_links.php --apply
 * On the server: docker exec vici-preview-php php bin/fix_vici_name_links.php [--apply]
 */

require __DIR__ . '/../vendor/autoload.php';

spl_autoload_register(function ($class) {
    $prefix = 'Vici\\';
    if (strncmp($class, $prefix, 5) === 0) {
        $file = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, 5)) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

// load .env the same way the web bootstrap does (env vars are not set for
// docker exec sessions)
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        putenv(trim($k) . '=' . trim($v));
        $_ENV[trim($k)] = trim($v);
    }
}

$apply = in_array('--apply', $argv, true);
$db = new Vici\DB\DBConnector();

$pattern = '~href="(?:https?://(?:www\.)?vici\.org)?/vici/([^"#?]+)~i';
$stmt = $db->query("SELECT ptxt_pnt_id, ptxt_lang, ptxt_full FROM ptexts WHERE ptxt_full REGEXP '/vici/[^0-9/]'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// resolve every distinct non-numeric slug once
$lookup = $db->prepare("SELECT pnt_id FROM points WHERE pnt_name = ?");
$resolution = [];   // raw slug => pnt_id | 'ambiguous' | 'unresolved'
foreach ($rows as $row) {
    preg_match_all($pattern, $row['ptxt_full'], $m);
    foreach ($m[1] as $raw) {
        if (ctype_digit($raw) || isset($resolution[$raw])) continue;
        $name = str_replace('_', ' ', rawurldecode($raw));
        $lookup->execute([$name]);
        $hits = $lookup->fetchAll(PDO::FETCH_ASSOC);
        $resolution[$raw] = count($hits) === 1 ? (int)$hits[0]['pnt_id']
            : (count($hits) > 1 ? 'ambiguous' : 'unresolved');
    }
}

$update = $db->prepare("UPDATE ptexts SET ptxt_full = :full WHERE ptxt_pnt_id = :pnt AND ptxt_lang = :lang");
$rewrittenLinks = $rewrittenRows = 0;

foreach ($rows as $row) {
    $full = $row['ptxt_full'];
    $changed = false;
    preg_match_all($pattern, $full, $m);
    foreach (array_unique($m[1]) as $raw) {
        if (ctype_digit($raw)) continue;
        $target = $resolution[$raw];
        if (!is_int($target)) continue;
        $n = 0;
        $full = preg_replace(
            '~href="(?:https?://(?:www\.)?vici\.org)?/vici/' . preg_quote($raw, '~') . '~i',
            'href="/vici/' . $target,
            $full, -1, $n);
        $rewrittenLinks += $n;
        $changed = $changed || $n > 0;
    }
    if ($changed) {
        $rewrittenRows++;
        if ($apply) {
            $update->execute(['full' => $full, 'pnt' => $row['ptxt_pnt_id'], 'lang' => $row['ptxt_lang']]);
        }
    }
}

foreach ($resolution as $raw => $target) {
    if (!is_int($target)) {
        echo "manual review: $raw ($target)\n";
    }
}

echo ($apply ? 'Applied: ' : 'Dry run, would apply: ')
   . "$rewrittenLinks links in $rewrittenRows rows\n";
if (!$apply && $rewrittenRows > 0) {
    echo "Re-run with --apply to write.\n";
}
