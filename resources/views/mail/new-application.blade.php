@extends('mail.layout')

@section('content')
    <h1 style="margin:0 0 12px;font-size:20px;color:#f8fafc;">New application received</h1>

    <p style="margin:0 0 16px;color:#cbd5e1;">
        <strong style="color:#f8fafc;">{{ $application->fullName() }}</strong> has applied for
        <strong style="color:#f8fafc;">{{ $application->job?->title }}</strong>.
    </p>

    <table role="presentation" style="width:100%;border-collapse:collapse;">
        @foreach ([
            'Email' => $application->email,
            'Phone' => $application->phone ?? '—',
            'CV' => $application->cv_original_name,
            'Applied' => $application->created_at?->format('j F Y, H:i'),
        ] as $label => $value)
            <tr>
                <td style="padding:9px 0;color:#64748b;font-size:13px;border-top:1px solid #1e2b45;">{{ $label }}</td>
                <td style="padding:9px 0;color:#f8fafc;font-size:13px;text-align:right;border-top:1px solid #1e2b45;">{{ $value }}</td>
            </tr>
        @endforeach
    </table>

    @if ($application->cover_letter)
        <div style="margin-top:20px;padding:14px;border-radius:10px;background:#0f1729;border:1px solid #1e2b45;">
            <p style="margin:0 0 8px;color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:0.08em;">Cover letter</p>
            <p style="margin:0;color:#cbd5e1;font-size:13px;line-height:1.6;white-space:pre-line;">{{ $application->cover_letter }}</p>
        </div>
    @endif

    <p style="margin:24px 0 0;">
        <a href="{{ url('/admin/applications') }}"
           style="display:inline-block;padding:11px 18px;border-radius:999px;background:#3b82f6;color:#ffffff;font-size:13px;font-weight:600;text-decoration:none;">
            Review in the admin
        </a>
    </p>
@endsection
