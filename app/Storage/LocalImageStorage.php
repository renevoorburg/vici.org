<?php

namespace Vici\Storage;

class LocalImageStorage implements ImageStorage
{
    private string $baseDir;

    public function __construct()
    {
        $this->baseDir = dirname(__DIR__, 2) . '/public/uploads';
    }

    public function exists(string $filename): bool
    {
        return file_exists($this->baseDir . '/' . $filename);
    }

    public function put(string $filename, string $tmpPath): bool
    {
        $target = $this->baseDir . '/' . $filename;
        if (!rename($tmpPath, $target)) {
            return false;
        }
        chmod($target, 0644);
        return true;
    }
}
