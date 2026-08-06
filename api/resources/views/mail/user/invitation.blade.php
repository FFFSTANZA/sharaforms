@component('mail::message')

# Invitation to Join {{ $workspaceName }} on SharaForms

Hello,

You have been invited to join the workspace "{{ $workspaceName }}" on SharaForms, a platform that simplifies form creation and data collection. With SharaForms, you can easily create, distribute, and manage forms for any purpose.

To join us, please click the button below.

@component('mail::button', ['url' => $inviteLink])
Accept Invitation
@endcomponent

Looking forward to having you on board.

Best Regards,
The SharaForms Team

@endcomponent
