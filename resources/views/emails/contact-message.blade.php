@php($s = $submission)
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #0f172a; line-height: 1.5;">
    <h2 style="margin:0 0 4px;">{{ $s->isFeedback() ? 'New customer feedback' : 'New contact enquiry' }}</h2>
    <p style="margin:0 0 16px; color:#64748b;">Submitted via the Best CPMS website on {{ $s->created_at?->format('d M Y, H:i') }}.</p>

    <table cellpadding="6" cellspacing="0" style="border-collapse:collapse;">
        <tr><td style="color:#64748b;">Name</td><td><strong>{{ $s->name }}</strong></td></tr>
        <tr><td style="color:#64748b;">Email</td><td>{{ $s->email }}</td></tr>
        @if ($s->phone)<tr><td style="color:#64748b;">Phone</td><td>{{ $s->phone }}</td></tr>@endif
        @if ($s->company)<tr><td style="color:#64748b;">Company</td><td>{{ $s->company }}</td></tr>@endif
        @if ($s->subject)<tr><td style="color:#64748b;">Subject</td><td>{{ $s->subject }}</td></tr>@endif
        @if ($s->isFeedback() && $s->rating)<tr><td style="color:#64748b;">Rating</td><td>{{ $s->rating }} / 5</td></tr>@endif
    </table>

    <p style="margin:16px 0 4px; color:#64748b;">Message</p>
    <div style="white-space:pre-wrap; padding:12px 14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px;">{{ $s->message }}</div>

    <p style="margin-top:20px; color:#94a3b8; font-size:12px;">Reply directly to this email to respond to {{ $s->name }}.</p>
</body>
</html>
