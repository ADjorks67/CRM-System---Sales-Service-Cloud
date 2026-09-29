<?php

namespace App\Support;

class CrmValidation
{
    public const PHONE = ['nullable', 'string', 'max:40', 'regex:/^[\d\s\-()+]*$/'];

    public const WEBSITE = ['nullable', 'string', 'max:255', 'url'];

    public const EMAIL = ['nullable', 'email', 'max:80'];
}
