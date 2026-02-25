<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $recipient = $this->ensureRecipient($request);

        $query = Notification::query()
            ->where('user_id', $recipient->id)
            ->orderByDesc('id');

        $notifications = $query->paginate(12);
        $notifications->appends($request->query());
        $notifications = $this->appendIndexNumbers($notifications);

        return Inertia::render('Admin/Notifications/Index', [
            'notifications' => $notifications,
            'stats' => $this->getStats((int) $recipient->id),
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $recipient = $this->ensureRecipient($request);

        if ($request->boolean('lite')) {
            return response()->json($this->buildLiteFeed((int) $recipient->id));
        }

        return response()->json($this->buildFeed((int) $recipient->id, 8));
    }

    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        $recipient = $this->ensureRecipient($request);

        abort_unless((int) $notification->user_id === (int) $recipient->id, 403);

        if (! $notification->is_read) {
            $notification->forceFill([
                'is_read' => true,
                'read_at' => now(),
            ])->save();
        }

        return response()->json($this->buildFeed((int) $recipient->id, 8));
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $recipient = $this->ensureRecipient($request);

        Notification::query()
            ->where('user_id', $recipient->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json($this->buildFeed((int) $recipient->id, 8));
    }

    private function ensureRecipient(Request $request)
    {
        $user = $request->user();

        $allowed = $user
            && (
                (method_exists($user, 'isAdmin') && $user->isAdmin())
                || (method_exists($user, 'isPsychologist') && $user->isPsychologist())
            );

        abort_unless($allowed, 403);

        return $user;
    }

    private function buildFeed(int $adminId, int $limit = 8): array
    {
        $total = (int) Notification::query()
            ->where('user_id', $adminId)
            ->count();

        $unread = (int) Notification::query()
            ->where('user_id', $adminId)
            ->where('is_read', false)
            ->count();

        $items = Notification::query()
            ->where('user_id', $adminId)
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

    private function getStats(int $adminId): array
    {
        return [
            'total_count' => (int) Notification::query()->where('user_id', $adminId)->count(),
            'unread_count' => (int) Notification::query()->where('user_id', $adminId)->where('is_read', false)->count(),
        ];
    }

    private function buildLiteFeed(int $adminId): array
    {
        return [
            'unread_count' => (int) Notification::query()
                ->where('user_id', $adminId)
                ->where('is_read', false)
                ->count(),
            'latest_id' => (int) (Notification::query()
                ->where('user_id', $adminId)
                ->max('id') ?? 0),
        ];
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
}
