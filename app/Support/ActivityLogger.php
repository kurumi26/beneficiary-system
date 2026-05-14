<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public function log(string $action, string $description, ?Model $subject = null, array $properties = []): void
    {
        $log = ActivityLog::query()->create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
        ]);

        if ($subject !== null) {
            $log->subject()->associate($subject);
            $log->save();
        }
    }
}
