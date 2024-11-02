<?php

namespace App\Schedules;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DeleteExpiredBookings
{
    public function __invoke()
    {
        Booking::where(function ($query) {
            $query->where('date', '<', Carbon::now()->format('Y-m-d'))
                ->orWhere(function ($query) {
                    $query->where('date', '=', Carbon::now()->format('Y-m-d'))
                        ->where('end_time', '<=', Carbon::now()->format('H:i:s'));
                });
        })->delete();
    }
}
