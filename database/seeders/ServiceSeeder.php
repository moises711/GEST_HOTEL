<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            ['name' => 'Room Service', 'description' => 'Servicio a la habitación', 'price' => 10.00, 'active' => true],
            ['name' => 'Laundry', 'description' => 'Servicio de lavandería', 'price' => 5.50, 'active' => true],
            ['name' => 'Spa', 'description' => 'Acceso al spa y masajes', 'price' => 30.00, 'active' => true],
        ];

        foreach ($services as $s) {
            Service::updateOrCreate(['name' => $s['name']], $s);
        }
    }
}
