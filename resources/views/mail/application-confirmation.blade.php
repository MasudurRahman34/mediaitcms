@extends('mail.layout')

@section('content')
    <h1 style="margin:0 0 12px;font-size:20px;color:#f8fafc;">Thanks for applying, {{ $application->first_name }}</h1>

    <p style="margin:0 0 12px;color:#cbd5e1;">
        We have received your application for <strong style="color:#f8fafc;">{{ $application->job?->title }}</strong>.
        Our team will review your CV and get back to you if your experience matches what we need.
    </p>

    <table role="presentation" style="width:100%;margin-top:20px;border-collapse:collapse;">
        <tr>
            <td style="padding:10px 0;color:#64748b;font-size:13px;">Role</td>
            <td style="padding:10px 0;color:#f8fafc;font-size:13px;text-align:right;">{{ $application->job?->title }}</td>
        </tr>
        <tr>
            <td style="padding:10px 0;color:#64748b;font-size:13px;border-top:1px solid #1e2b45;">Submitted</td>
            <td style="padding:10px 0;color:#f8fafc;font-size:13px;text-align:right;border-top:1px solid #1e2b45;">
                {{ $application->created_at?->format('j F Y, H:i') }}
            </td>
        </tr>
    </table>

    <p style="margin:24px 0 0;color:#94a3b8;font-size:13px;">
        No further action is needed from you at this stage.
    </p>
@endsection
