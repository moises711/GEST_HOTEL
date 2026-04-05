<?php

namespace App\Http\Controllers\Admin;

use App\Models\hotel;
use App\Models\Plan;
use Illuminate\Support\Str;

trait ResolvesPlanConfiguration
{
    protected function resolveHotelAndPlan($user): array
    {
        $hotel = $user?->hotels()->with('plans')->first();
        if (!$hotel) {
            return [null, null];
        }

        $activePlan = $hotel->plans->first(function ($plan) {
            return (bool) ($plan->pivot->active ?? false);
        });

        if (!$activePlan) {
            $activePlan = $hotel->plans
                ->sortByDesc(function ($plan) {
                    return $plan->pivot->ends_at ?? null;
                })
                ->first();
        }

        return [$hotel, $activePlan];
    }

    protected function enabledModulesForHotel(hotel $hotel, ?Plan $plan): array
    {
        $hotelModules = is_array($hotel->modules) ? $hotel->modules : [];
        if (!empty($hotelModules)) {
            return array_values(array_unique($hotelModules));
        }

        return is_array($plan?->modules) ? array_values(array_unique($plan->modules)) : [];
    }

    protected function planFeatures(?Plan $plan): array
    {
        return is_array($plan?->features) ? array_values(array_unique($plan->features)) : [];
    }

    protected function moduleIsEnabled(array $modules, string $module): bool
    {
        return in_array($module, $modules, true);
    }

    protected function resolveExperienceConfiguration(?Plan $plan): array
    {
        $name = Str::lower($plan?->name ?? '');
        $features = $this->planFeatures($plan);

        $tier = 'basico';
        if (Str::contains($name, ['intermedio', 'pro'])) {
            $tier = 'intermedio';
        }
        if (Str::contains($name, ['alto', 'empresarial', 'premium'])) {
            $tier = 'alto_empresarial';
        }

        return [
            'tier' => $tier,
            'plan_name' => $plan?->name ?? 'Sin plan',
            'max_users' => $plan?->max_users,
            'features' => $features,
            'has_weekly_charts' => in_array('dashboard_weekly_occupancy', $features, true) || in_array('kpis_advanced', $features, true),
            'has_smart_alerts' => in_array('smart_alerts', $features, true),
            'has_dark_mode' => in_array('dark_mode', $features, true),
            'has_advanced_kpis' => in_array('kpis_advanced', $features, true),
        ];
    }
}
