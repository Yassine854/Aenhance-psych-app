<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Appointment Confirmed</title>
</head>
<body style="margin:0;background:#f3f6fb;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#0f172a;">
@php
    $localStart = $start ? $start->copy()->timezone(config('app.timezone')) : null;
    $localEnd = $end ? $end->copy()->timezone(config('app.timezone')) : null;
    $month = $localStart ? strtoupper($localStart->format('M')) : '--';
    $day = $localStart ? $localStart->format('d') : '--';
    $dow = $localStart ? $localStart->format('l') : '--';
    $timeRange = $localStart && $localEnd
        ? $localStart->format('H:i').' - '.$localEnd->format('H:i').' ('.config('app.timezone').')'
        : '--';

    $utcStart = $start ? $start->copy()->utc()->format('Ymd\\THis\\Z') : '';
    $utcEnd = $end ? $end->copy()->utc()->format('Ymd\\THis\\Z') : '';
    $title = rawurlencode('Therapy appointment confirmed #'.$appointment->id);
    $details = rawurlencode('Appointment with '.($counterpart->name ?? 'your counterpart').' for '.($attendeeName ?? 'the patient').' via '.config('app.name'));
    $location = rawurlencode(config('app.url'));
    $googleCalendarUrl = $utcStart && $utcEnd
        ? 'https://calendar.google.com/calendar/render?action=TEMPLATE&text='.$title.'&dates='.$utcStart.'/'.$utcEnd.'&details='.$details.'&location='.$location
        : null;
    $outlookCalendarUrl = $utcStart && $utcEnd
        ? 'https://outlook.live.com/calendar/0/deeplink/compose?subject='.$title.'&startdt='.$utcStart.'&enddt='.$utcEnd.'&body='.$details.'&location='.$location
        : null;
@endphp

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" width="640" style="max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 18px 45px rgba(15,23,42,0.10);">
                    <tr>
                        <td style="padding:28px 28px 22px;background:linear-gradient(135deg,#5997ac 0%,#af5166 100%);color:#ffffff;">
                            <div style="font-size:12px;letter-spacing:0.12em;opacity:.9;text-transform:uppercase;font-weight:700;">{{ config('app.name') }}</div>
                            <h1 style="margin:8px 0 0;font-size:27px;line-height:1.2;font-weight:800;">Appointment Confirmed</h1>
                            <p style="margin:10px 0 0;font-size:15px;opacity:.95;">A calendar reminder is ready for you.</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:26px 28px 10px;">
                            <p style="margin:0 0 16px;font-size:15px;color:#334155;">Hello <strong>{{ $recipient->name }}</strong>, your appointment <strong>#{{ $appointment->id }}</strong> is confirmed.</p>

                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;background:#f8fafc;">
                                <tr>
                                    <td width="108" valign="top" style="padding:16px 12px;background:#ffffff;border-right:1px solid #e2e8f0;text-align:center;">
                                        <div style="font-size:11px;font-weight:800;letter-spacing:.12em;color:#64748b;">{{ $month }}</div>
                                        <div style="font-size:34px;line-height:1.1;font-weight:900;color:#0f172a;">{{ $day }}</div>
                                        <div style="font-size:12px;color:#64748b;">{{ $dow }}</div>
                                    </td>
                                    <td valign="top" style="padding:16px 18px;">
                                        <div style="font-size:13px;color:#64748b;margin-bottom:4px;">Time</div>
                                        <div style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:12px;">{{ $timeRange }}</div>

                                        <div style="font-size:13px;color:#64748b;margin-bottom:4px;">With</div>
                                        <div style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:12px;">{{ $counterpart->name ?? 'Your counterpart' }}</div>

                                        <div style="font-size:13px;color:#64748b;margin-bottom:4px;">Appointment for</div>
                                        <div style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:12px;">{{ $attendeeName ?? ($appointment->patient->name ?? 'The patient') }}</div>

                                        @if(!empty($bookingContext))
                                            <div style="font-size:13px;color:#64748b;margin-bottom:4px;">Booking details</div>
                                            <div style="font-size:15px;font-weight:600;color:#0f172a;">{{ $bookingContext }}</div>
                                        @else
                                            <div style="font-size:13px;color:#64748b;margin-bottom:4px;">Booking details</div>
                                            <div style="font-size:15px;font-weight:600;color:#0f172a;">Booked for the patient account owner</div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 28px 10px;">
                            <p style="margin:0 0 10px;font-size:14px;color:#334155;"><strong>Calendar reminder:</strong> this email includes an attached <strong>.ics</strong> file. Open it to add the event to Apple Calendar, Google Calendar, Outlook, or your phone calendar app.</p>
                            @if($googleCalendarUrl || $outlookCalendarUrl)
                                <table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:12px;">
                                    <tr>
                                        @if($googleCalendarUrl)
                                            <td style="padding-right:8px;padding-bottom:8px;">
                                                <a href="{{ $googleCalendarUrl }}" target="_blank" rel="noopener" style="display:inline-block;padding:10px 14px;border-radius:10px;background:#0f172a;color:#ffffff;text-decoration:none;font-size:13px;font-weight:700;">Add to Google Calendar</a>
                                            </td>
                                        @endif
                                        @if($outlookCalendarUrl)
                                            <td style="padding-bottom:8px;">
                                                <a href="{{ $outlookCalendarUrl }}" target="_blank" rel="noopener" style="display:inline-block;padding:10px 14px;border-radius:10px;background:#ffffff;color:#0f172a;text-decoration:none;font-size:13px;font-weight:700;border:1px solid #cbd5e1;">Add to Outlook</a>
                                            </td>
                                        @endif
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 28px 26px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-top:1px solid #e2e8f0;padding-top:14px;">
                                <tr>
                                    <td style="font-size:12px;color:#64748b;line-height:1.6;">
                                        Please join on time. If the appointment is updated later, a new invite may be sent.
                                        <br>
                                        {{ config('app.name') }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
