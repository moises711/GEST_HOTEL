<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Str;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $shared = [
            ...parent::share($request),
        ];

        if ($request->user()) {
            $user = $request->user()->load(['hotels.plans']);
            $planConfig = null;

            $hotel = $user->hotels->first();
            if ($hotel) {
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

                $enabledModules = is_array($hotel->modules) && !empty($hotel->modules)
                    ? array_values(array_unique($hotel->modules))
                    : (is_array($activePlan?->modules) ? array_values(array_unique($activePlan->modules)) : []);

                $normalizedPlanName = Str::lower($activePlan?->name ?? '');
                $tier = 'basico';
                if (Str::contains($normalizedPlanName, ['intermedio', 'pro'])) {
                    $tier = 'intermedio';
                }
                if (Str::contains($normalizedPlanName, ['alto', 'empresarial', 'premium'])) {
                    $tier = 'alto_empresarial';
                }

                $planConfig = [
                    'tier' => $tier,
                    'plan_name' => $activePlan?->name ?? 'Sin plan',
                    'max_users' => $activePlan?->max_users,
                    'enabled_modules' => $enabledModules,
                ];
            }

            $shared['auth'] = [
                'user' => $user,
            ];
            $shared['planConfig'] = $planConfig;
        } else {
            $shared['auth'] = [
                'user' => null,
            ];
            $shared['planConfig'] = null;
        }

        return $shared;
    }
}
