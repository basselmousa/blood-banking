@component('mail::message')
# Hello my name is {{ $requesterName }}
I am contacting you for blood request type {{ $blood }} from user {{ $user }}
his phone is {{ $phone }}
and his email {{ $email }}

## Can you contacting him and be a hero for saving my life ?

## My information

Email : {{ $requesterEmail }}
Name : {{ $requesterName }}


Thanks,<br>
{{ config('mail.from.name') }}
@endcomponent
