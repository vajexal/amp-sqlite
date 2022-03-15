<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite;

class OpenConnectionRequest
{
    public function __construct(
        private string $filename,
        private int $flags,
        private string $encryptionKey
    ) {
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getFlags(): int
    {
        return $this->flags;
    }

    public function getEncryptionKey(): string
    {
        return $this->encryptionKey;
    }
}
