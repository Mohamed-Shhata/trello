<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput as OutputConsoleOutput;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Task::factory()->count(40)->create();
        $tasks = Task::all();
        foreach ($tasks as $task) {
            // $output = new OutputConsoleOutput();
            $projectSupervisorId = Project::where('id', $task->projectId)->pluck('supervisorId')->first();

            $employeeId = User::where('supervisorId', $projectSupervisorId)->pluck('id');

            if (count($employeeId) != 0) {
                Task::where('id', $task->id)->update(array('employeeId' => $employeeId->random()));
            }
        }
    }
}
