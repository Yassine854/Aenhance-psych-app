<?php

namespace App\Mail;

use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment,
        public User $recipient,
        public User $counterpart
    ) {
    }

    public function build(): self
    {
        $start = $this->appointment->scheduled_start ? Carbon::parse($this->appointment->scheduled_start) : null;
        $end = $this->appointment->scheduled_end ? Carbon::parse($this->appointment->scheduled_end) : null;

        return $this
            ->subject('Appointment cancelled #'.$this->appointment->id)
            ->view('emails.appointments.cancelled')
            ->with([
                'appointment' => $this->appointment,
                'recipient' => $this->recipient,
                'counterpart' => $this->counterpart,
                'start' => $start,
                'end' => $end,
            ])
            ->attachData(
                $this->buildCalendarCancel(),
                'appointment-'.$this->appointment->id.'.ics',
                ['mime' => 'text/calendar; charset=UTF-8; method=CANCEL']
            );
    }

    private function buildCalendarCancel(): string
    {
        $appName = (string) config('app.name', 'Psych App');
        $appUrl = (string) config('app.url', 'http://localhost');
        $timezone = (string) config('app.timezone', 'UTC');
        $fromName = $this->escapeIcsText((string) config('mail.from.name', $appName));
        $fromAddress = (string) config('mail.from.address', 'no-reply@example.com');

        $start = $this->appointment->scheduled_start
            ? Carbon::parse($this->appointment->scheduled_start, $timezone)->utc()
            : Carbon::now('UTC');

        $end = $this->appointment->scheduled_end
            ? Carbon::parse($this->appointment->scheduled_end, $timezone)->utc()
            : $start->copy()->addHour();

        $uid = 'appointment-'.$this->appointment->id.'@'.parse_url($appUrl, PHP_URL_HOST);
        $dtStamp = Carbon::now('UTC')->format('Ymd\\THis\\Z');
        $dtStart = $start->format('Ymd\\THis\\Z');
        $dtEnd = $end->format('Ymd\\THis\\Z');

        $summary = $this->escapeIcsText('Therapy appointment cancelled');
        $description = $this->escapeIcsText(
            'Appointment #'.$this->appointment->id.
            ' between '.$this->recipient->name.' and '.$this->counterpart->name.
            ' was cancelled.'
        );
        $location = $this->escapeIcsText('Online / '.$appUrl);
        $recipientName = $this->escapeIcsText((string) $this->recipient->name);
        $recipientEmail = trim((string) ($this->recipient->email ?? ''));

        return implode("\r\n", [
            'BEGIN:VCALENDAR',
            'PRODID:-//'.$appName.'//Appointments//EN',
            'VERSION:2.0',
            'CALSCALE:GREGORIAN',
            'METHOD:CANCEL',
            'BEGIN:VEVENT',
            'UID:'.$uid,
            'DTSTAMP:'.$dtStamp,
            'DTSTART:'.$dtStart,
            'DTEND:'.$dtEnd,
            'SUMMARY:'.$summary,
            'DESCRIPTION:'.$description,
            'LOCATION:'.$location,
            'URL:'.$this->escapeIcsText($appUrl),
            'STATUS:CANCELLED',
            'TRANSP:OPAQUE',
            'ORGANIZER;CN='.$fromName.':mailto:'.$fromAddress,
            $recipientEmail !== '' ? ('ATTENDEE;CN='.$recipientName.';ROLE=REQ-PARTICIPANT:mailto:'.$recipientEmail) : '',
            'SEQUENCE:0',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);
    }

    private function escapeIcsText(string $value): string
    {
        return str_replace(
            ["\\", ";", ",", "\r\n", "\r", "\n"],
            ["\\\\", "\\;", "\\,", "\\n", "\\n", "\\n"],
            $value
        );
    }
}
