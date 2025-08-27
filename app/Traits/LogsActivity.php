<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            static::logActivity($model, 'created');
        });

        static::updated(function ($model) {
            static::logActivity($model, 'updated');
        });

        static::deleted(function ($model) {
            static::logActivity($model, 'deleted');
        });
    }

    protected static function logActivity($model, string $action)
    {
        // Hentikan logging jika aplikasi berjalan dari console (misal: seeder, command)
        if (app()->runningInConsole()) {
            return;
        }
    
        ActivityLog::create([
            'user_id'       => Auth::id(),
            'action'        => $action,
            'description'   => static::getLogDescription($model, $action),
            'loggable_id'   => $model->id,
            'loggable_type' => get_class($model),
            'changes'       => $action === 'updated' ? static::getDirtyChanges($model) : null,
            'ip_address'    => request()->ip(),
            'user_agent'    => request()->userAgent(),
        ]);
    }

    protected static function getLogDescription($model, string $action): string
    {
        $modelName = class_basename($model);
        $userName = Auth::user()?->name ?? 'Sistem';
        $identifier = $model->name ?? $model->booking_number ?? $model->client_name ?? "#{$model->id}";

        return "{$userName} {$action} {$modelName} '{$identifier}'.";
    }

    // ================== PERUBAHAN NAMA METODE DI SINI ==================
    // Diubah dari getChanges() menjadi getDirtyChanges() untuk menghindari konflik
    protected static function getDirtyChanges($model): ?array
    {
        $changes = [];
        // getDirty() akan mengembalikan atribut yang berubah
        foreach ($model->getDirty() as $attribute => $newValue) {
            // Kita tidak perlu mencatat perubahan 'updated_at'
            if ($attribute === 'updated_at') {
                continue;
            }
            
            // getOriginal() akan mengembalikan nilai asli sebelum diubah
            $oldValue = $model->getOriginal($attribute);

            $changes[$attribute] = [
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        return count($changes) > 0 ? $changes : null;
    }
}