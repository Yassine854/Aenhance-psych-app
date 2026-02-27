<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Report;
use App\Models\User;
use App\Models\PatientProfile;
use App\Models\PsychologistProfile;
use App\Models\Notification;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\JsonResponse;
use App\Services\ActivityLogger;

class ReportController extends Controller
{
    /**
     * Store a new report.
     */
    public function store(StoreReportRequest $request): JsonResponse
    {
        $data = $request->only([
            'reporter_id',
            'reporter_type',
            'reported_id',
            'reported_type',
            'reason',
        ]);

        if ($request->hasFile('proof_image')) {
            $file = $request->file('proof_image');

            $result = Cloudinary::uploadFile($file->getRealPath(), [
                'folder' => 'reports',
                'resource_type' => 'image',
            ]);

            if (is_object($result) && method_exists($result, 'getSecurePath')) {
                $data['proof_image'] = $result->getSecurePath();
            } elseif (is_array($result) && isset($result['secure_url'])) {
                $data['proof_image'] = $result['secure_url'];
            } else {
                $response = is_object($result) && method_exists($result, 'getResponse') ? $result->getResponse() : null;
                $data['proof_image'] = $response['secure_url'] ?? null;
            }
        }

        $report = Report::create($data);

        ActivityLogger::log(auth()->id() ?? $data['reporter_id'] ?? null, auth()->user()?->role ?? $data['reporter_type'] ?? null, 'created_report', 'Report', $report->id, $data['reason'] ?? null);

        // Notify admins about the new report (include reporter and reported display names)
        try {
            $resolveName = function ($type, $id) {
                if (! $type || ! $id) return null;
                $t = strtolower(trim((string) $type));

                // Prefer resolving by declared type to avoid matching a User with same numeric id.
                if (str_contains($t, 'patient')) {
                    $p = PatientProfile::find($id);
                    if ($p) {
                        return $p->user?->name ?? trim((string) (($p->first_name ?? '') . ' ' . ($p->last_name ?? ''))) ?: ('Patient #'.$p->id);
                    }
                }

                if (str_contains($t, 'psychologist')) {
                    $pp = PsychologistProfile::find($id);
                    if ($pp) {
                        return $pp->user?->name ?? trim((string) (($pp->first_name ?? '') . ' ' . ($pp->last_name ?? ''))) ?: ('Psychologist #'.$pp->id);
                    }
                }

                // Fallback to User when type is unknown or profile not found.
                $u = User::find($id);
                if ($u) return $u->name ?? ('User #'.$u->id);
                return 'User #'.(int) $id;
            };

            $reporterName = $resolveName($report->reporter_type, $report->reporter_id) ?? ('Reporter #'.(int) $report->reporter_id);
            $reportedName = $resolveName($report->reported_type, $report->reported_id) ?? ('Reported #'.(int) $report->reported_id);

            $adminIds = User::query()->whereRaw('UPPER(role) = ?', ['ADMIN'])->pluck('id')->map(fn($id) => (int) $id)->all();
            if (! empty($adminIds)) {
                $now = now();
                $message = sprintf('%s reported %s. Reason: %s', $reporterName, $reportedName, $report->reason ?? '');
                $payload = [
                    'event_type' => 'report_created',
                    'report_id' => (int) $report->id,
                    'reporter' => ['type' => $report->reporter_type, 'id' => (int) $report->reporter_id, 'name' => $reporterName],
                    'reported' => ['type' => $report->reported_type, 'id' => (int) $report->reported_id, 'name' => $reportedName],
                ];

                $rows = [];
                foreach ($adminIds as $adminId) {
                    $rows[] = [
                        'user_id' => (int) $adminId,
                        'title' => 'New report submitted',
                        'message' => $message,
                        'type' => 'report',
                        'channel' => 'in_app',
                        'action_url' => '/admin/reports',
                        'data' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                        'is_read' => false,
                        'read_at' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                Notification::query()->insert($rows);
            }
        } catch (\Throwable $e) {
            // don't fail report creation for notification errors
        }

        return response()->json(['data' => $report], 201);
    }
}
