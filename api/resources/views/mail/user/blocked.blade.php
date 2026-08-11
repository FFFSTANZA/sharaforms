@component('mail::message')
# Your Account Has Been Blocked

Hello {{ $user->name }},

Your account has been temporarily blocked by our automated security system.

**Reason:**
{{ $reason }}

## Think This is an Error?

If you believe this is a mistake, please contact us immediately at **{{ config('links.contact_email') }}** with:

- **Clear explanation** of your form's purpose
- **Screenshots** or detailed description of your form content
- **Business context** or use case for your form
- **Any supporting documentation** that proves legitimacy

**The more proof you provide, the faster we can review and unblock your account.**

Our team reviews appeals quickly during business hours, and legitimate accounts are typically restored within a few hours of providing adequate justification.

@component('mail::button', ['url' => 'mailto:' . config('links.contact_email')])
Contact Support via Email
@endcomponent

Thanks,<br>
{{ config('app.name') }} Security Team
@endcomponent