<?php

namespace App\Helpers;

use App\Models\Shift;
use Illuminate\Support\Facades\Session;

class ShiftHelper
{
    /**
     * Get the active shift ID from session.
     */
    public static function getActiveShiftId(): ?int
    {
        return Session::get('active_shift_id');
    }

    /**
     * Get the active shift model.
     */
    public static function getActiveShift(): ?Shift
    {
        $id = self::getActiveShiftId();
        return $id ? Shift::find($id) : null;
    }

    /**
     * Set the active shift in session.
     */
    public static function setActiveShift(?int $shiftId): void
    {
        if ($shiftId) {
            Session::put('active_shift_id', $shiftId);
        } else {
            Session::forget('active_shift_id');
        }
    }

    /**
     * Automatically determine shift by current time.
     */
    public static function determineShiftByTime(): ?Shift
    {
        $now = now()->format('H:i:s');

        // Try to find a shift that covers the current time
        $shift = Shift::where('jam_mulai', '<=', $now)
            ->where('jam_selesai', '>', $now)
            ->first();

        // If no shift found (e.g., night time 21:01 - 06:59), you might want to return null or fallback
        // The user asked for specific shifts, so we'll fallback to the closest or first.
        return $shift ?? Shift::orderBy('jam_mulai')->first();
    }

    /**
     * Check if the current user should use shift functionality.
     * Only users with the 'kasir' role should use shifts.
     */
    public static function shouldUseShift(): bool
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // super_admin, admin, and petugas_stok should NOT use shifts.
        // It's safer to only allow 'kasir' to use shifts.
        return $user->hasRole('kasir');
    }
}
