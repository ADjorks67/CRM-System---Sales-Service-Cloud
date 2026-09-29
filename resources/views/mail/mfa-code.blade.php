<x-mail::message>
# Verification code

Hello {{ $notifiable->name }},

Your one-time sign-in code is:

**{{ $code }}**

This code expires in 10 minutes. If you did not try to sign in, contact an administrator.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
