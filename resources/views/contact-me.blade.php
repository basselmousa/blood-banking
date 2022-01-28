@component('mail::message')
# {{ $full_name }}

{{ $message }}

### {{ $email }}

Thanks,<br>
{{ config('mail.from.name') }}
@endcomponent
