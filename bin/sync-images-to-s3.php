<?php
/**
 * Sync image files from a local directory to S3-compatible object
 * storage. The object key is the path relative to the given source
 * root, so it matches the img_path values in the database minus the
 * leading slash:
 *
 *   /srv/static/uploads/foo.jpg            -> uploads/foo.jpg
 *   /srv/static/livius/a/b/c.jpg           -> livius/a/b/c.jpg
 *
 * Existing objects are skipped, so the script is resumable.
 *
 * Usage: php bin/sync-images-to-s3.php [--dry-run] [--jobs=N] <source-root> [subdir...]
 *
 * Without subdir arguments everything under source-root is synced;
 * pass e.g. "uploads livius" to restrict it to those top-level dirs.
 *
 * Reads S3_ENDPOINT, S3_REGION, S3_BUCKET, S3_ACCESS_KEY and
 * S3_SECRET_KEY from the environment (or .env).
 */

require dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$args = $argv;
array_shift($args);
$dryRun = in_array('--dry-run', $args, true);
$jobs = 20;
foreach ($args as $i => $a) {
    if (str_starts_with($a, '--jobs=')) {
        $jobs = max(1, (int)substr($a, 7));
        unset($args[$i]);
    }
}
$args = array_values(array_diff($args, ['--dry-run']));
$sourceRoot = $args[0] ?? null;
$subdirs = array_slice($args, 1);

if (!$sourceRoot || !is_dir($sourceRoot)) {
    fwrite(STDERR, "Usage: php bin/sync-images-to-s3.php [--dry-run] <source-root> [subdir...]\n");
    exit(1);
}
$sourceRoot = rtrim($sourceRoot, '/');

$roots = [$sourceRoot];
if ($subdirs) {
    $roots = [];
    foreach ($subdirs as $sub) {
        $dir = sprintf('%s/%s', $sourceRoot, trim($sub, '/'));
        if (!is_dir($dir)) {
            fwrite(STDERR, "Subdir not found: {$dir}\n");
            exit(1);
        }
        $roots[] = $dir;
    }
}

foreach (['S3_ENDPOINT', 'S3_BUCKET', 'S3_ACCESS_KEY', 'S3_SECRET_KEY'] as $var) {
    if (empty($_ENV[$var])) {
        fwrite(STDERR, "Missing environment variable {$var}\n");
        exit(1);
    }
}

$client = new Aws\S3\S3Client([
    'version' => 'latest',
    'region' => $_ENV['S3_REGION'] ?? 'EU',
    'endpoint' => $_ENV['S3_ENDPOINT'],
    'credentials' => [
        'key' => $_ENV['S3_ACCESS_KEY'],
        'secret' => $_ENV['S3_SECRET_KEY'],
    ],
    // non-AWS backends: bucket name goes in the path, and the
    // default CRC32 checksums of recent SDKs are not supported
    'use_path_style_endpoint' => true,
    'request_checksum_calculation' => 'when_required',
    'response_checksum_validation' => 'when_required',
]);
$bucket = $_ENV['S3_BUCKET'];

echo "Scanning {$sourceRoot}…\n";
$paths = [];
foreach ($roots as $root) {
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $file) {
        if ($file->isFile() && !str_starts_with($file->getFilename(), '.')) {
            $pathname = $file->getPathname();
            $paths[] = [$pathname, ltrim(substr($pathname, strlen($sourceRoot)), '/')];
        }
    }
}
$total = count($paths);
echo "{$total} candidate files\n";

// prefetch existing keys: one paginated listing is much faster than a
// HEAD request per file
echo "Listing existing objects in {$bucket}…\n";
$existing = [];
$prefixes = $subdirs ? array_map(fn($s) => trim($s, '/') . '/', $subdirs) : [''];
foreach ($prefixes as $prefix) {
    foreach ($client->getPaginator('ListObjectsV2', ['Bucket' => $bucket, 'Prefix' => $prefix]) as $page) {
        foreach ($page['Contents'] ?? [] as $obj) {
            $existing[$obj['Key']] = true;
        }
    }
}
echo count($existing) . " objects already in bucket\n";

$pending = [];
$skipped = 0;
foreach ($paths as [$pathname, $key]) {
    if (isset($existing[$key])) {
        $skipped++;
    } else {
        $pending[] = [$pathname, $key];
    }
}
$total = count($pending);
echo "{$total} files to upload, {$skipped} already present\n";

if ($dryRun) {
    echo "[dry-run] would upload {$total} files, {$skipped} already present\n";
    exit(0);
}

$done = $failed = 0;
$progress = function () use (&$done, &$failed, $total) {
    $n = $done + $failed;
    if ($n % 250 === 0 || $n === $total) {
        echo "{$n}/{$total}: {$done} uploaded, {$failed} failed\n";
    }
};

// lazy generator: commands are built as the pool drains, keeping
// memory flat over 100k+ files
$commands = (function () use ($pending, $client, $bucket) {
    foreach ($pending as [$pathname, $key]) {
        yield $key => $client->getCommand('PutObject', [
            'Bucket' => $bucket,
            'Key' => $key,
            'SourceFile' => $pathname,
            'ContentType' => mime_content_type($pathname) ?: 'application/octet-stream',
        ]);
    }
})();

$pool = new Aws\CommandPool($client, $commands, [
    'concurrency' => $jobs,
    'fulfilled' => function ($result, $key) use (&$done, $progress) {
        $done++;
        $progress();
    },
    'rejected' => function ($reason, $key) use (&$failed, $progress) {
        $failed++;
        $msg = $reason instanceof \Throwable ? $reason->getMessage() : var_export($reason, true);
        fwrite(STDERR, "FAILED {$key}: {$msg}\n");
        $progress();
    },
]);
$pool->promise()->wait();

echo "done: {$done} uploaded, {$skipped} already present, {$failed} failed\n";
exit($failed > 0 ? 1 : 0);
