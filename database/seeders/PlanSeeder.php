<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $plans = [
            ['name' => 'Básico', 'aliases' => ['Básico', 'Basico'], 'description' => 'Plan básico para hoteles pequeños', 'price' => 49.00, 'duration_months' => 1, 'active' => true,
                'modules' => ['rooms','reservations','guests'],
                'max_users' => 1,
                'features' => ['dashboard_simple','calendar_basic','status_colors','quick_search','limited_history','simple_confirmations']
            ],
            ['name' => 'Intermedio', 'aliases' => ['Intermedio', 'Pro'], 'description' => 'Plan intermedio con operación multiusuario', 'price' => 99.00, 'duration_months' => 1, 'active' => true,
                'modules' => ['rooms','reservations','guests','housekeeping','users','notes'],
                'max_users' => 3,
                'features' => ['dashboard_weekly_occupancy','quick_actions','notifications_checkin_checkout','notifications_housekeeping','multiuser_limited','full_history','quick_state_changes','agile_navigation']
            ],
            ['name' => 'Alto / Empresarial', 'aliases' => ['Alto / Empresarial', 'Premium', 'Alto', 'Empresarial'], 'description' => 'Plan avanzado con control total y analítica', 'price' => 199.00, 'duration_months' => 1, 'active' => true,
                'modules' => ['rooms','reservations','guests','housekeeping','users','notes','analytics','maintenance','customization','dark_mode'],
                'max_users' => null,
                'features' => ['kpis_advanced','multi_view_calendar_list_cards','customization_colors_logo_widgets','dark_mode','user_activity','smart_alerts','full_module_control','full_view_control','full_history','light_state_automation']
            ],
        ];

        foreach ($plans as $p) {
            $aliases = $p['aliases'];
            unset($p['aliases']);

            $existing = Plan::whereIn('name', $aliases)->first();
            if ($existing) {
                $existing->update($p);
            } else {
                Plan::create($p);
            }
        }
    }
}
