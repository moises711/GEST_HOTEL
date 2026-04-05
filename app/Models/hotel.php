<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class hotel extends Model

{
    protected $fillable = [
        'name',
        'location',
        'description',
        'id_cliente',
        'id_user',
        'database',
        'modules',
    ];

    protected $casts = [
        'modules' => 'array',
        'settings' => 'array',
    ];

    public function owners()
    {
        return $this->belongsToMany(User::class, 'hotel_user', 'hotel_id', 'user_id')->withPivot('role')->withTimestamps();
    }

    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'hotel_plan', 'hotel_id', 'plan_id')->withPivot(['starts_at','ends_at','active','pending'])->withTimestamps();
    }

    public function currentPlan()
    {
        return $this->plans()->wherePivot('active', true)->orderByDesc('hotel_plan.ends_at');
    }

    /**
     * Aplica un plan al hotel: activa pivot, reemplaza módulos y guarda configuración del plan.
     */
    public function applyPlan(\App\Models\Plan $plan, $force = false, ?string $startsAt = null, ?string $endsAt = null)
    {
        $startsAt = $startsAt ?? now()->toDateString();
        $endsAt = $endsAt ?? now()->addMonths($plan->duration_months ?? 1)->toDateString();

        // detach previous active plans
        foreach ($this->plans()->wherePivot('active', true)->get() as $p) {
            $this->plans()->updateExistingPivot($p->id, ['active' => false]);
        }

        // attach or update pivot
        if ($this->plans()->where('plan_id', $plan->id)->exists()) {
            $this->plans()->updateExistingPivot($plan->id, [
                'active' => true,
                'pending' => false,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ]);
        } else {
            $this->plans()->attach($plan->id, [
                'active' => true,
                'pending' => false,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ]);
        }

        // replace modules to reflect selected plan exactly
        $this->modules = array_values(array_unique($plan->modules ?? []));

        // update settings: max_users, features and subscription profile
        $settings = $this->settings ?? [];
        $settings['max_users'] = $plan->max_users;
        $settings['features'] = array_values(array_unique($plan->features ?? []));
        $settings['subscription_profile'] = $this->resolveSubscriptionProfile($plan);

        $this->settings = $settings;
        $this->save();

        return $this;
    }

    public function markPlanAsPending(\App\Models\Plan $plan): void
    {
        $startsAt = now()->toDateString();
        $endsAt = now()->addMonths($plan->duration_months ?? 1)->toDateString();

        if (!$this->plans()->where('plan_id', $plan->id)->exists()) {
            $this->plans()->attach($plan->id, [
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'active' => false,
                'pending' => true,
            ]);
            return;
        }

        $this->plans()->updateExistingPivot($plan->id, [
            'pending' => true,
            'active' => false,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);
    }

    private function resolveSubscriptionProfile(\App\Models\Plan $plan): array
    {
        $normalized = Str::lower($plan->name ?? '');

        if (Str::contains($normalized, ['básico', 'basico'])) {
            return [
                'tier' => 'basico',
                'dashboard' => 'simple',
                'views' => ['calendar_basic'],
                'status_colors' => true,
                'notifications' => 'basic',
            ];
        }

        if (Str::contains($normalized, ['intermedio', 'pro'])) {
            return [
                'tier' => 'intermedio',
                'dashboard' => 'with_weekly_charts',
                'views' => ['calendar_basic', 'list'],
                'status_colors' => true,
                'notifications' => 'checkin_checkout_housekeeping',
            ];
        }

        return [
            'tier' => 'alto_empresarial',
            'dashboard' => 'advanced_kpis',
            'views' => ['calendar', 'list', 'cards'],
            'status_colors' => true,
            'notifications' => 'smart_alerts',
            'customization' => true,
            'dark_mode' => true,
            'automation' => 'light',
        ];
    }
}
