<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\hotel;
use App\Services\HotelPlanService;

class ApplyPendingPlans extends Command
{
    protected $signature = 'hotel:apply-pending-plans';
    protected $description = 'Aplica planes pendientes a hoteles y propaga módulos';

    public function __construct(private HotelPlanService $hotelPlanService)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Buscando planes pendientes...');

        $hotels = hotel::with(['plans'])->get();

        $applied = 0;

        foreach ($hotels as $hotel) {
            $pending = $hotel->plans()->wherePivot('pending', true)->get();
            foreach ($pending as $plan) {
                $this->info("Aplicando plan {$plan->name} a hotel {$hotel->name}");

                // use hotel->applyPlan to handle merging and settings
                try {
                    $this->hotelPlanService->applyNow($hotel, $plan);
                } catch (\Exception $e) {
                    report($e);
                }

                $applied++;
            }
        }

        $this->info("Planes pendientes aplicados: {$applied}");
        return 0;
    }
}
