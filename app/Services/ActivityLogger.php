<?php

namespace App\Services;

use App\Models\Log;

class ActivityLogger
{
    /**
     * Create a log entry.
     *
     * @param int|null $actorId
     * @param string|null $actorRole
     * @param string $action
     * @param string|null $targetType
     * @param int|null $targetId
     * @param string|null $description
     * @return \App\Models\Log
     */
    public static function log($actorId, $actorRole, $action, $targetType = null, $targetId = null, $description = null)
    {
        return Log::create([
            'actor_id' => $actorId,
            'actor_role' => $actorRole,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'description' => $description,
        ]);
    }
}
