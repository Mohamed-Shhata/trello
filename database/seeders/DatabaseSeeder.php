<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $this->call(UserSeeder::class);

        $this->call(ProjectSeeder::class);
        $this->call(TaskSeeder::class);
        $projects = Project::all();
        foreach ($projects as $project) {
            $countOfCompletedTask = count(Task::where('projectId', $project->id)->where('isCompleted', 1)->get());
            Project::where('id', $project->id)->update(array('numberOfCompletedTasks' => $countOfCompletedTask));
        }
    }
}
