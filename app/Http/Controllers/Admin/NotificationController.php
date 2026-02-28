<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $recipient = $this->ensureRecipient($request);

        if ($this->isPatientRecipient($recipient)) {
            return $this->indexForPatient($request, $recipient);
        }

        if ($this->isPsychologistRecipient($recipient)) {
            return $this->indexForPsychologist($request, $recipient);
        }

        $query = $this->notificationsQueryForRecipient($recipient)
            ->orderByDesc('id');

        $notifications = $query->paginate(12);
        $notifications->appends($request->query());
        $notifications = $this->appendIndexNumbers($notifications);

        return Inertia::render('Admin/Notifications/Index', [
            'notifications' => $notifications,
            'stats' => $this->getStats($recipient),
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $recipient = $this->ensureRecipient($request);

        if ($request->boolean('lite')) {
            return response()->json($this->buildLiteFeed($recipient));
        }

        return response()->json($this->buildFeed($recipient, 8));
    }

    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        $recipient = $this->ensureRecipient($request);

        $targetNotification = $this->notificationsQueryForRecipient($recipient)
            ->whereKey($notification->id)
            ->first();

        abort_unless((bool) $targetNotification, 403);

        if ($this->isPatientRecipient($recipient)) {
            abort_unless($this->isNotificationRelatedToPatient($targetNotification, (int) $recipient->id), 403);
        }

        if (! $targetNotification->is_read) {
            $targetNotification->forceFill([
                'is_read' => true,
                'read_at' => now(),
            ])->save();
        }

        return response()->json($this->buildFeed($recipient, 8));
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $recipient = $this->ensureRecipient($request);

        if ($this->isPatientRecipient($recipient)) {
            $matchingIds = $this->matchingPatientNotificationIds($recipient);

            if (! empty($matchingIds)) {
                Notification::query()
                    ->whereIn('id', $matchingIds)
                    ->where('is_read', false)
                    ->update([
                        'is_read' => true,
                        'read_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            return response()->json($this->buildFeed($recipient, 8));
        }

        $this->notificationsQueryForRecipient($recipient)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json($this->buildFeed($recipient, 8));
    }

    private function ensureRecipient(Request $request)
    {
        $user = $request->user();

        $allowed = $user
            && (
                (method_exists($user, 'isAdmin') && $user->isAdmin())
                || (method_exists($user, 'isPsychologist') && $user->isPsychologist())
                || (method_exists($user, 'isPatient') && $user->isPatient())
            );

        abort_unless($allowed, 403);

        return $user;
    }

    private function buildFeed($recipient, int $limit = 8): array
    {
        if ($this->isPatientRecipient($recipient)) {
            $recipientId = (int) ($recipient->id ?? 0);

            $items = $this->notificationsQueryForRecipient($recipient)
                ->latest('id')
                ->get();

            $filtered = $items
                ->filter(fn (Notification $notification) => $this->isNotificationRelatedToPatient($notification, $recipientId))
                ->values();

            $total = (int) $filtered->count();
            $unread = (int) $filtered->where('is_read', false)->count();

            $notifications = $filtered
                ->take($limit)
                ->values()
                ->map(function (Notification $notification, int $index) use ($total) {
                    return [
                        'id' => $notification->id,
                        'index_no' => max(1, $total - $index),
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'type' => $notification->type,
                        'action_url' => $notification->action_url ?: '/notifications',
                        'is_read' => (bool) $notification->is_read,
                        'read_at' => optional($notification->read_at)->toIso8601String(),
                        'created_at' => optional($notification->created_at)->toIso8601String(),
                        'time_ago' => optional($notification->created_at)->diffForHumans(),
                    ];
                })->all();

            return [
                'notifications' => $notifications,
                'total_count' => $total,
                'unread_count' => $unread,
                'latest_id' => $notifications[0]['id'] ?? 0,
            ];
        }

        $query = $this->notificationsQueryForRecipient($recipient);

        $total = (int) (clone $query)->count();

        $unread = (int) (clone $query)
            ->where('is_read', false)
            ->count();

        $items = (clone $query)
            ->latest('id')
            ->limit($limit)
            ->get();

        $notifications = $items->values()->map(function (Notification $notification, int $index) use ($total) {
            return [
                'id' => $notification->id,
                'index_no' => max(1, $total - $index),
                'title' => $notification->title,
                'message' => $notification->message,
                'type' => $notification->type,
                'action_url' => $notification->action_url ?: '/notifications',
                'is_read' => (bool) $notification->is_read,
                'read_at' => optional($notification->read_at)->toIso8601String(),
                'created_at' => optional($notification->created_at)->toIso8601String(),
                'time_ago' => optional($notification->created_at)->diffForHumans(),
            ];
        })->all();

        return [
            'notifications' => $notifications,
            'total_count' => $total,
            'unread_count' => $unread,
            'latest_id' => $notifications[0]['id'] ?? 0,
        ];
    }

    private function getStats($recipient): array
    {
        if ($this->isPatientRecipient($recipient)) {
            $recipientId = (int) ($recipient->id ?? 0);

            $filtered = $this->notificationsQueryForRecipient($recipient)
                ->latest('id')
                ->get()
                ->filter(fn (Notification $notification) => $this->isNotificationRelatedToPatient($notification, $recipientId));

            return [
                'total_count' => (int) $filtered->count(),
                'unread_count' => (int) $filtered->where('is_read', false)->count(),
            ];
        }

        return [
            'total_count' => (int) $this->notificationsQueryForRecipient($recipient)->count(),
            'unread_count' => (int) $this->notificationsQueryForRecipient($recipient)->where('is_read', false)->count(),
        ];
    }

    private function buildLiteFeed($recipient): array
    {
        if ($this->isPatientRecipient($recipient)) {
            $recipientId = (int) ($recipient->id ?? 0);

            $filtered = $this->notificationsQueryForRecipient($recipient)
                ->latest('id')
                ->get()
                ->filter(fn (Notification $notification) => $this->isNotificationRelatedToPatient($notification, $recipientId))
                ->values();

            return [
                'unread_count' => (int) $filtered->where('is_read', false)->count(),
                'latest_id' => (int) ($filtered->first()->id ?? 0),
            ];
        }

        $query = $this->notificationsQueryForRecipient($recipient);

        return [
            'unread_count' => (int) (clone $query)
                ->where('is_read', false)
                ->count(),
            'latest_id' => (int) ((clone $query)
                ->max('id') ?? 0),
        ];
    }

    private function notificationsQueryForRecipient($recipient): Builder
    {
        $recipientId = (int) ($recipient->id ?? 0);

        $query = Notification::query()
            ->where('user_id', $recipientId);

        if ($this->isPatientRecipient($recipient)) {
            return $query->where('action_url', 'like', '/patient%');
        }

        if ($this->isPsychologistRecipient($recipient)) {
            return $query->where('action_url', 'like', '/psychologist%');
        }

        return $query;
    }

    private function isPatientRecipient($recipient): bool
    {
        $role = strtoupper(trim((string) ($recipient->role ?? '')));

        return $role === 'PATIENT';
    }

    private function isPsychologistRecipient($recipient): bool
    {
        $role = strtoupper(trim((string) ($recipient->role ?? '')));

        return $role === 'PSYCHOLOGIST';
    }

    private function appendIndexNumbers($notifications)
    {
        $total = (int) $notifications->total();
        $offset = ((int) $notifications->currentPage() - 1) * (int) $notifications->perPage();

        $notifications->getCollection()->transform(function (Notification $notification, int $index) use ($total, $offset) {
            return [
                'id' => $notification->id,
                'index_no' => max(1, $total - ($offset + $index)),
                'title' => $notification->title,
                'message' => $notification->message,
                'type' => $notification->type,
                'channel' => $notification->channel,
                'action_url' => $notification->action_url ?: '/notifications',
                'is_read' => (bool) $notification->is_read,
                'read_at' => optional($notification->read_at)->toIso8601String(),
                'created_at' => optional($notification->created_at)->toIso8601String(),
                'time_ago' => optional($notification->created_at)->diffForHumans(),
            ];
        });

        return $notifications;
    }

    private function indexForPatient(Request $request, $recipient): Response
    {
        $perPage = 12;
        $page = max(1, (int) $request->integer('page', 1));
        $recipientId = (int) ($recipient->id ?? 0);

        $items = $this->notificationsQueryForRecipient($recipient)
            ->latest('id')
            ->get()
            ->filter(fn (Notification $notification) => $this->isNotificationRelatedToPatient($notification, $recipientId))
            ->values();

        $total = (int) $items->count();
        $offset = ($page - 1) * $perPage;

        $mapped = $items
            ->slice($offset, $perPage)
            ->values()
            ->map(function (Notification $notification, int $index) use ($total, $offset) {
                return [
                    'id' => $notification->id,
                    'index_no' => max(1, $total - ($offset + $index)),
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'channel' => $notification->channel,
                    'action_url' => $notification->action_url ?: '/notifications',
                    'is_read' => (bool) $notification->is_read,
                    'read_at' => optional($notification->read_at)->toIso8601String(),
                    'created_at' => optional($notification->created_at)->toIso8601String(),
                    'time_ago' => optional($notification->created_at)->diffForHumans(),
                ];
            })->all();

        $notifications = new LengthAwarePaginator($mapped, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return Inertia::render('Patient/Notifications/Index', [
            'notifications' => $notifications,
            'stats' => [
                'total_count' => $total,
                'unread_count' => (int) $items->where('is_read', false)->count(),
            ],
        ]);
    }

    private function indexForPsychologist(Request $request, $recipient): Response
    {
        $query = $this->notificationsQueryForRecipient($recipient)
            ->orderByDesc('id');

        $notifications = $query->paginate(12);
        $notifications->appends($request->query());
        $notifications = $this->appendIndexNumbers($notifications);

        return Inertia::render('Psychologist/Notifications/Index', [
            'notifications' => $notifications,
            'stats' => $this->getStats($recipient),
        ]);
    }

    private function matchingPatientNotificationIds($recipient): array
    {
        $recipientId = (int) ($recipient->id ?? 0);

        return $this->notificationsQueryForRecipient($recipient)
            ->latest('id')
            ->get()
            ->filter(fn (Notification $notification) => $this->isNotificationRelatedToPatient($notification, $recipientId))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    private function isNotificationRelatedToPatient(Notification $notification, int $recipientId): bool
    {
        $actionUrl = (string) ($notification->action_url ?? '');
        if (! str_starts_with($actionUrl, '/patient')) {
            return false;
        }

        $data = $this->normalizeNotificationData($notification);

        $reported = $data['reported'] ?? null;
        if (is_array($reported)) {
            $reportedType = strtolower(trim((string) ($reported['type'] ?? '')));
            $reportedId = (int) ($reported['id'] ?? 0);

            if ($reportedType === 'patient' && $reportedId === $recipientId) {
                return true;
            }
        }

        $reporter = $data['reporter'] ?? null;
        if (is_array($reporter)) {
            $reporterType = strtolower(trim((string) ($reporter['type'] ?? '')));
            $reporterId = (int) ($reporter['id'] ?? 0);

            if ($reporterType === 'patient' && $reporterId === $recipientId) {
                return true;
            }
        }

        $patientId = (int) ($data['patient_id'] ?? 0);
        if ($patientId === $recipientId) {
            return true;
        }

        return false;
    }

    private function normalizeNotificationData(Notification $notification): array
    {
        $value = $notification->data;

        if (is_array($value)) {
            return $value;
        }

        $raw = $notification->getRawOriginal('data');
        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded) && ! is_string($decoded)) {
            return [];
        }

        if (is_array($decoded)) {
            return $decoded;
        }

        $decodedTwice = json_decode($decoded, true);
        if (! is_array($decodedTwice)) {
            return [];
        }

        return $decodedTwice;
    }
}
