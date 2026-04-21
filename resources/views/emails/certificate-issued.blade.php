<x-mail::message>
    # Congratulations, {{ $user->first_name }}!

    Your certificate has been issued for completing the module **{{ $module->module_title ?? 'N/A' }}**.

    ## Certificate Details

    - **Certificate Number:** {{ $certificate->certificate_number }}
    - **Module:** {{ $module->module_title ?? 'N/A' }}
    - **Course:** {{ $course->course_name ?? 'N/A' }}
    - **Issue Date:** {{ $certificate->issue_date?->format('F d, Y') ?? now()->format('F d, Y') }}
    - **Recipient:** {{ $user->full_name }}

    <x-mail::button :url="$viewUrl">
        View Certificate
    </x-mail::button>

    You can also download your certificate as a PDF from your dashboard.

    Thank you for your dedication and hard work!

    Best regards,<br>
    {{ config('app.name') }} Team
</x-mail::message>