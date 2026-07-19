<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; background:#fff; margin:0; padding:0; }
    .container { max-width:600px; margin:24px auto; border:1px solid #eee; border-radius:8px; overflow:hidden; }
    .header { background: linear-gradient(90deg,#ff6b6b,#ff3b3b); color:#fff; padding:18px 24px; }
    .title { font-size:18px; font-weight:700; margin:0 0 6px 0; }
    .subtitle { font-size:13px; opacity:0.95; margin:0; }
    .body { padding:20px 24px; color:#333; }
    .meta { background:#fff6f6; border:1px solid #ffd6d6; padding:12px; border-radius:6px; margin:12px 0; }
    .label { font-weight:600; color:#b00020; }
    .footer { padding:14px 24px; font-size:12px; color:#777; background:#fff; }
    a.button { display:inline-block; background:#ff3b3b; color:#fff; padding:10px 14px; border-radius:6px; text-decoration:none; margin-top:8px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <p class="title">Appointment Cancelled</p>
      <p class="subtitle">We're sorry — an appointment has been cancelled.</p>
    </div>
    <div class="body">
      <p>Hi {{ $recipient->name }},</p>
      <p>We wanted to let you know that <strong>appointment #{{ $appointment->id }}</strong> with <strong>{{ $counterpart->name }}</strong> has been cancelled.</p>

      <div class="meta">
        <p><span class="label">Appointment for:</span> {{ $attendeeName ?? ($appointment->patient->name ?? 'The patient') }}</p>
        <p><span class="label">Booking details:</span> {{ $bookingContext ?: 'Booked for the patient account owner.' }}</p>
        <p><span class="label">When:</span>
        @if($start)
          {{ $start->format('l, j M Y H:i') }} - {{ $end ? $end->format('H:i') : '' }}
        @else
          To be decided
        @endif
        </p>
        @if(!empty($appointment->cancellation_reason))
          <p><span class="label">Reason:</span> {{ $appointment->cancellation_reason }}</p>
        @endif
        <p><span class="label">Cancelled by:</span> {{ $appointment->canceled_by ?? 'Unknown' }}</p>
      </div>

      <p>If this cancellation was a mistake or you'd like to reschedule, please visit the appointment page or contact support.</p>
      <p><a class="button" href="{{ config('app.url') }}">Open App</a></p>
    </div>
    <div class="footer">
      <p>Thanks,<br>{{ config('app.name') }} team</p>
    </div>
  </div>
</body>
</html>
