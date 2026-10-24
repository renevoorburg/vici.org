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
 * Usage: php bin/sync-images-to-s3.php [--dry-run] <source-root>
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
$args = array_values(array_diff($args, ['--dry-run']));
$sourceRoot = $args[0] ?? null;

if (!$sourceRoot || !is_dir($sourceRoot)) {
    fwrite(STDERR, "Usage: php bin/sync-images-to-s3.php [--dry-run] <source-root>\n");
    exit(1);
}
$sourceRoot = rtrim($sourceRoot, '/');

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

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceRoot, FilesystemIterator::SKIP_DOTS)
);

$done = $skipped = $failed = 0;
foreach ($files as $file) {
    if (!$file->isFile() || str_starts_with($file->getFilename(), '.')) {
        continue;
    }
    $key = ltrim(substr($file->getPathname(), strlen($sourceRoot)), '/');
    if ($client->doesObjectExistV2($bucket, $key)) {
        $skipped++;
        continue;
    }
    if ($dryRun) {
        $done++;
        continue;
    }
    try {
        $client->putObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'SourceFile' => $file->getPathname(),
            'ContentType' => mime_content_type($file->getPathname()) ?: 'application/octet-stream',
        ]);
        $done++;
    } catch (\Throwable $e) {
        $failed++;
        fwrite(STDERR, "FAILED {$key}: {$e->getMessage()}\n");
    }
    if (($done + $skipped + $failed) % 500 === 0) {
        echo "{$done} uploaded, {$skipped} skipped, {$failed} failed\n";
    }
}

echo ($dryRun ? '[dry-run] ' : '') . "done: {$done} uploaded, {$skipped} already present, {$failed} failed\n";
exit($failed > 0 ? 1 : 0);
