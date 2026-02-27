<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Notification;
use App\Models\PsychologistProfile;
use App\Models\User;
use Illuminate\Support\Str;

class NotificationService
{
    public static function notifyNewRegistration(User $registeredUser): void
    {
        $role = strtoupper(trim((string) $registeredUser->role));

        if (! in_array($role, ['PATIENT', 'PSYCHOLOGIST'], true)) {
            return;
        }

        $adminIds = User::query()
            ->whereRaw('UPPER(role) = ?', ['ADMIN'])
            ->pluck('id');

        if ($adminIds->isEmpty()) {
            return;
        }

        $isPsychologist = $role === 'PSYCHOLOGIST';
        $title = $isPsychologist
            ? 'New psychologist registration'
            : 'New patient registration';

        $message = sprintf(
            '%s (%s) created a %s account.',
            (string) $registeredUser->name,
            (string) $registeredUser->email,
            strtolower($role)
        );

        $payload = [
            'registered_user_id' => $registeredUser->id,
            'registered_user_role' => $role,
            'registered_user_name' => $registeredUser->name,
            'registered_user_email' => $registeredUser->email,
        ];

        $now = now();
        $rows = [];

        foreach ($adminIds as $adminId) {
            $rows[] = [
                'user_id' => (int) $adminId,
                'title' => $title,
                'message' => $message,
                'type' => 'registration',
                'channel' => 'in_app',
                'action_url' => '/notifications',
                'data' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'is_read' => false,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Notification::query()->insert($rows);
    }

    public static function notifyAppointmentConfirmed(Appointment $appointment): void
    {
        $adminIds = User::query()
            ->whereRaw('UPPER(role) = ?', ['ADMIN'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $psychologistId = (int) ($appointment->psychologist_id ?? 0);

        $recipientActionMap = [];

        foreach ($adminIds as $adminId) {
            $recipientActionMap[$adminId] = '/admin/appointments';
        }

        if ($psychologistId > 0) {
            $recipientActionMap[$psychologistId] = '/psychologist/appointments';
        }

        if (empty($recipientActionMap)) {
            return;
        }

        $eventKey = (string) Str::uuid();
        $scheduledStart = optional($appointment->scheduled_start)->toIso8601String();
        $scheduledEnd = optional($appointment->scheduled_end)->toIso8601String();

        $title = 'Appointment confirmed';
        $message = 'Appointment #'.$appointment->id.' has been confirmed.';

        $sharedPayload = [
            'event_key' => $eventKey,
            'event_type' => 'appointment_confirmed',
            'appointment_id' => (int) $appointment->id,
            'patient_id' => (int) ($appointment->patient_id ?? 0),
            'psychologist_id' => $psychologistId,
            'status' => 'confirmed',
            'scheduled_start' => $scheduledStart,
            'scheduled_end' => $scheduledEnd,
        ];

        $now = now();
        $rows = [];

        foreach ($recipientActionMap as $userId => $actionUrl) {
            $rows[] = [
                'user_id' => (int) $userId,
                'title' => $title,
                'message' => $message,
                'type' => 'appointment',
                'channel' => 'in_app',
                'action_url' => $actionUrl,
                'data' => json_encode($sharedPayload, JSON_UNESCAPED_UNICODE),
                'is_read' => false,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Notification::query()->insert($rows);
    }

    public static function notifyPsychologistApproved(User $psychologistUser): void
    {
        $role = strtoupper(trim((string) $psychologistUser->role));
        if ($role !== 'PSYCHOLOGIST') {
            return;
        }

        Notification::query()->create([
            'user_id' => (int) $psychologistUser->id,
            'title' => 'Profile approved',
            'message' => 'Your psychologist profile has been approved. Please send the remaining verification documents by email.',
            'type' => 'verification',
            'channel' => 'in_app',
            'action_url' => '/notifications',
            'data' => [
                'event_type' => 'psychologist_profile_approved',
                'psychologist_user_id' => (int) $psychologistUser->id,
            ],
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    public static function notifyAppointmentCancelledByPsychologist(Appointment $appointment, ?string $reason = null): void
    {
        $adminIds = User::query()
            ->whereRaw('UPPER(role) = ?', ['ADMIN'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $psychologistId = (int) ($appointment->psychologist_id ?? 0);
        $patientId = (int) ($appointment->patient_id ?? 0);
        $psychologist = $psychologistId > 0
            ? User::query()->find($psychologistId, ['id', 'name'])
            : null;

        $psychologistName = trim((string) ($psychologist?->name ?? ''));
        if ($psychologistName === '' && $psychologistId > 0) {
            $profile = PsychologistProfile::query()
                ->where('user_id', $psychologistId)
                ->first(['first_name', 'last_name']);

            if ($profile) {
                $psychologistName = trim((string) (($profile->first_name ?? '').' '.($profile->last_name ?? '')));
            }
        }

        if ($psychologistName === '') {
            $psychologistName = $psychologistId > 0 ? ('User #'.$psychologistId) : 'Unknown psychologist';
        }

        $message = 'Appointment #'.$appointment->id.' was cancelled by psychologist '.$psychologistName.'.';
        if (is_string($reason) && trim($reason) !== '') {
            $message .= ' Reason: '.trim($reason);
        }

        $payload = [
            'event_type' => 'appointment_cancelled_by_psychologist',
            'appointment_id' => (int) $appointment->id,
            'patient_id' => (int) ($appointment->patient_id ?? 0),
            'psychologist_id' => $psychologistId,
            'psychologist_name' => $psychologistName,
            'reason' => $reason,
            'status' => 'cancelled',
        ];

        $now = now();
        $rows = [];

        foreach ($adminIds as $adminId) {
            $rows[] = [
                'user_id' => $adminId,
                'title' => 'Appointment cancelled',
                'message' => $message,
                'type' => 'appointment',
                'channel' => 'in_app',
                'action_url' => '/admin/appointments',
                'data' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'is_read' => false,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($patientId > 0) {
            $rows[] = [
                'user_id' => $patientId,
                'title' => 'Appointment cancelled',
                'message' => $message,
                'type' => 'appointment',
                'channel' => 'in_app',
                'action_url' => '/patient/appointments',
                'data' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'is_read' => false,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Notification::query()->insert($rows);
    }
}
