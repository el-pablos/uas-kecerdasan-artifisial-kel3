<?php

/**
 * Log an activity for audit trail.
 */
if (!function_exists('activity_log')) {
    function activity_log(string $action, string $entityType, ?string $entityId, string $description): void
    {
        try {
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'description' => $description,
            ]);
        } catch (\Throwable $e) {
            // Silently fail — audit logging should never break the app
            \Illuminate\Support\Facades\Log::warning("Activity log failed: {$e->getMessage()}");
        }
    }
}
