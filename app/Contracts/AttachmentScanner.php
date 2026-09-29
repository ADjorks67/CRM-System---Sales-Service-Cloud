<?php

namespace App\Contracts;

interface AttachmentScanner
{
    /**
     * @return array{status: string, message?: string}
     */
    public function scan(string $absolutePath, string $originalName): array;
}
