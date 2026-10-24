<?php

namespace Vici\Storage;

/**
 * Where uploaded image files are stored. Two implementations:
 * the local public/uploads dir (default, dev) and S3-compatible
 * object storage (S3_* env vars, preview/production).
 */
interface ImageStorage
{
    /**
     * Whether an object already exists for the given filename.
     */
    public function exists(string $filename): bool;

    /**
     * Store the given file under the given filename.
     */
    public function put(string $filename, string $tmpPath): bool;
}
