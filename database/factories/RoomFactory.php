<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'name' => 'Room-' . fake()->unique()->numberBetween(1, 999),
            'capacity' => 20,
            'status' => 1,
        ];
    }
}
