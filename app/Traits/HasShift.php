<?php

namespace App\Traits;

use App\Models\Shift;
use App\Helpers\ShiftHelper;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasShift
{
    protected static function bootHasShift()
    {
        static::creating(function ($model) {
            if (!ShiftHelper::shouldUseShift()) {
                $model->shift_id = null;
                return;
            }

            if (!$model->shift_id) {
                // Try from session first
                $shiftId = ShiftHelper::getActiveShiftId();

                // If not in session, determine by time
                if (!$shiftId) {
                    $shift = ShiftHelper::determineShiftByTime();
                    $shiftId = $shift?->id;
                }

                $model->shift_id = $shiftId;
            }
        });
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
