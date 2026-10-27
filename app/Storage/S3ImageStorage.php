<?php

namespace Vici\Storage;

use Aws\S3\S3Client;

/**
 * S3-compatible object storage (TransIP Object Store / AWS S3).
 * Objects are stored under the "uploads/" prefix so keys match
 * the img_path values stored in the database.
 */
class S3ImageStorage implements ImageStorage
{
    private S3Client $client;
    private string $bucket;
    private string $prefix;

    public function __construct()
    {
        $this->client = new S3Client([
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
        $this->bucket = $_ENV['S3_BUCKET'];
        $this->prefix = rtrim($_ENV['S3_PREFIX'] ?? 'uploads', '/') . '/';
    }

    public function exists(string $filename): bool
    {
        return $this->client->doesObjectExistV2($this->bucket, $this->prefix . $filename);
    }

    public function put(string $filename, string $tmpPath): bool
    {
        try {
            $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $this->prefix . $filename,
                'SourceFile' => $tmpPath,
                'ContentType' => mime_content_type($tmpPath) ?: 'application/octet-stream',
            ]);
        } catch (\Throwable $e) {
            error_log('S3ImageStorage: ' . $e->getMessage());
            return false;
        }
        @unlink($tmpPath);
        return true;
    }
}
