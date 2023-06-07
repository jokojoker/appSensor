<?php

namespace Database\Factories\Sensor;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sensor\TemperatureSensor>
 */
class TemperatureSensorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        {
            return [
                'sensor_uuid' => fake()->uuid(),
                'ip_address' => fake()->ipv4(),
                'name' => fake()->monthName(),
                'manufacturer' => fake()->colorName()
            ];
        }
    }
}
