@component('mail::message')
# Verification code

Hi,

Use this code to verify your device:

**{{ $phone->auth_code }}**

Thanks,
{{ config('app.name') }}
@endcomponent