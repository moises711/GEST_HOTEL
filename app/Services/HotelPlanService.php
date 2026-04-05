<?php

namespace App\Services;

use App\Models\hotel;
use App\Models\Plan;
use Carbon\Carbon;

class HotelPlanService
{
    public function applyNow(hotel $hotel, Plan $plan, ?string $startDate = null, ?string $endDate = null, ?int $durationMonths = null): hotel
    {
        $startsAt = $startDate ? Carbon::parse($startDate)->toDateString() : now()->toDateString();
        $months = $durationMonths && $durationMonths > 0 ? $durationMonths : ($plan->duration_months ?? 1);
        $endsAt = $endDate
            ? Carbon::parse($endDate)->toDateString()
            : Carbon::parse($startsAt)->addMonths($months)->toDateString();

        return $hotel->applyPlan($plan, true, $startsAt, $endsAt);
    }

    public function markPending(hotel $hotel, Plan $plan): void
    {
        $hotel->markPlanAsPending($plan);
    }
}
