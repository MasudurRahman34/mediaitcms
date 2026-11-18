@extends('mail.layout')

@section('content')
    <h1 style="margin:0 0 12px;font-size:20px;color:#f8fafc;">New website enquiry</h1>

    <table role="presentation" style="width:100%;border-collapse:collapse;">
        @foreach ([
            'Name' => $enquiry->name,
            'Email' => $enquiry->email,
            'Phone' => $enquiry->phone ?? '—',
            'Company' => $enquiry->company ?? '—',
            'Subject' => $enquiry->subject ?? '—',
        ] as $label => $value)
            <tr>
                <td style="padding:9px 0;color:#64748b;font-size:13px;border-top:1px solid #1e2b45;">{{ $label }}</td>
                <td style="padding:9px 0;color:#f8fafc;font-size:13px;text-align:right;border-top:1px solid #1e2b45;">{{ $value }}</td>
            </tr>
        @endforeach
    </table>

    <div style="margin-top:20px;padding:14px;border-radius:10px;background:#0f1729;border:1px solid #1e2b45;">
        <p style="margin:0;color:#cbd5e1;font-size:13px;line-height:1.6;white-space:pre-line;">{{ $enquiry->message }}</p>
    </div>

    <p style="margin:24px 0 0;color:#94a3b8;font-size:13px;">
        Reply directly to this email to respond to {{ $enquiry->name }}.
    </p>
@endsection
