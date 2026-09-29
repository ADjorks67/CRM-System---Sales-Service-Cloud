<?php

namespace App\Services;

use App\Contracts\AttachmentScanner;

class EicarAttachmentScanner implements AttachmentScanner
{
    public const EICAR = 'X5O!P%@AP[4\\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*';

    public function scan(string $absolutePath, string $originalName): array
    {
        if (! is_readable($absolutePath)) {
            return ['status' => 'rejected', 'message' => 'File is not readable.'];
        }

        $contents = file_get_contents($absolutePath);
        if ($contents === false) {
            return ['status' => 'rejected', 'message' => 'Unable to read file.'];
        }

        if (str_contains($contents, self::EICAR) || str_contains($contents, 'EICAR-STANDARD-ANTIVIRUS-TEST-FILE')) {
            return ['status' => 'rejected', 'message' => 'Malware signature detected (EICAR).'];
        }

        return ['status' => 'clean'];
    }
}
