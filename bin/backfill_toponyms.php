<?php
/**
 * Backfill points.pnt_country / points.pnt_place via reverse geocoding.
 * Run: docker exec vici-novo-php php bin/backfill_toponyms.php
 * On the server: docker exec vici-preview-php php bin/backfill_toponyms.php
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

$db = new Vici\DB\DBConnector();
$stmt = $db->query("SELECT pnt_id, pnt_lat, pnt_lng FROM points WHERE pnt_country IS NULL ORDER BY pnt_id");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = count($rows);
echo "Backfilling $total points...\n";

$update = $db->prepare("UPDATE points SET pnt_country = :country, pnt_place = :place WHERE pnt_id = :id");
$i = 0;
foreach ($rows as $row) {
    $geocoder = new Vici\Service\ReverseGeocoder((float)$row['pnt_lat'], (float)$row['pnt_lng']);
    $country = $geocoder->getCountryCode() ?: null;
    $place = $geocoder->getNearbyPlace() ?: null;
    $update->execute(['country' => $country, 'place' => $place, 'id' => $row['pnt_id']]);
    if (++$i % 500 === 0) {
        echo "$i / $total\n";
    }
}
echo "Done: $i points backfilled.\n";
