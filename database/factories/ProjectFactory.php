<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $arr = array('easy', 'medium', 'hard');
        return [
            'name' => $this->faker->name,
            'complexity' => $arr[mt_rand(0, count($arr) - 1)],
            'deadLine' => Carbon::now()->addMonth(),
            'description' => $this->faker->text,
            'supervisorId' => User::where('isAdmin', 1)->pluck('id')->random(),
        ];
    }
}
