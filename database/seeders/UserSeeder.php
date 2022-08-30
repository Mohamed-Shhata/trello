<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->count(35)->create();
        $users = User::all();
        foreach ($users as $user) {
            $supervisorId = User::where('isAdmin', 1)->get()->pluck('id')->random();
            User::where('id', $user->id)->update(array('supervisorId' => $supervisorId));
        }
    }
}
