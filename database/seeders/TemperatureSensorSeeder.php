<?php

namespace Database\Seeders;

use App\Models\Sensor\TemperatureSensor;
use Database\Factories\Sensor\TemperatureSensorFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TemperatureSensorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TemperatureSensor::factory(5)->create();
    }
}
