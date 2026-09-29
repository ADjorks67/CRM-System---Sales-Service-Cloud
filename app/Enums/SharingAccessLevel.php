<?php

namespace App\Enums;

enum SharingAccessLevel: string
{
    case Private = 'private';
    case PublicReadOnly = 'public_read_only';
    case PublicReadWrite = 'public_read_write';

    public function label(): string
    {
        return match ($this) {
            self::Private => 'Private',
            self::PublicReadOnly => 'Public Read Only',
            self::PublicReadWrite => 'Public Read/Write',
        };
    }

    public function allowsReadForEveryone(): bool
    {
        return $this !== self::Private;
    }

    public function allowsWriteForEveryone(): bool
    {
        return $this === self::PublicReadWrite;
    }
}
