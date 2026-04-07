<x-mail::message>
# Your Bulk PDF Export is Ready

The bulk PDF generation for institution cheques has been completed successfully.

You can download the file using the link below:

<x-mail::button :url="$signedUrl">
Download PDF
</x-mail::button>

*Note: This link will expire in 1 hour.*

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
