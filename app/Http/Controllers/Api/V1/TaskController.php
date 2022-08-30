<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignEmployeeRequest;
use App\Http\Requests\CompleteTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;


class TaskController extends Controller
{
    /**
     * Display a listing of the tasks to authenticated user that is is supervisor in or employee.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userId = auth()->id();
        //tasks as supervisor
        $projectsAsSupervisor = Project::where("supervisorId",  $userId)->get();
        $tasks = [];
        $counter = 0;

        foreach ($projectsAsSupervisor as $project) {
            $task = Task::where('projectId', $project->id)->get();
            $tasks[$counter] = $task;
            $counter++;
        }
        //tasks as employee

        $employees =  User::where("id",  $userId)->pluck('supervisorId');
        foreach ($employees  as $employee) {
            $projects = Project::where("supervisorId",  $employee)->get();
            foreach ($projects as $project) {
                $task = Task::where('projectId', $project->id)->get();
                $tasks[$counter] = $task;
                $counter++;
            }
        }
        if ($counter == 0) {
            return response()->json([
                'status' => true,
                'message' => 'you have no tasks',
            ], 200);
        } else {
            return response()->json([
                'status' => true,
                'tasks' => $tasks,
            ], 200);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created task in storage.
     *
     * @param  \App\Http\Requests\StoreTaskRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTaskRequest $request)
    {
        $projectId = $request->projectId;
        $projectSupervisorId = Project::findOrFail($projectId)->supervisorId;

        if ($projectSupervisorId != NULL && $projectSupervisorId == auth()->id()) {
            $Project = Task::create([
                'name' => $request->name,
                // 'complexity' => $request->complexity,
                'description' => $request->description,
                'employeeId' => NULL,
                'projectId' => $request->projectId,
            ]);
            return response()->json([
                'status' => true,
                'message' => "Project Created successfully!",
                'Project' => $Project,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "can't find this project !",
            ],  404);
        }
    }
    /**
     * show all tasks in a specific project
     *
     * 
     * @return \Illuminate\Http\Response
     */
    public function projectTasks()
    {
        $id = $_GET['id'];

        if (count(Task::select('*')->where("projectId",  $id)->get())) {
            $tasks = Task::select('*')->where('projectId', '=', $id)->get();
            return response()->json([
                'status' => true,
                'Project' => $tasks,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Project has no tasks",
            ]);
        }
    }

    /**
     * Display the specified specific task if you are autherized to get.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        $taskId = $task->id;
        $projectId = Task::Where('id', $taskId)->first()->projectId;
        if ($projectId) {
            $userId = auth()->id();

            $projectSupervisorId = Project::where('id', $projectId)->first()->supervisorId;

            if ($projectSupervisorId == $userId || count(User::where('id', $userId)->where("supervisorId",  $projectSupervisorId)->get())) {
                $task = Task::where('id', $taskId)->first();
                return response()->json([
                    'status' => true,
                    'Project' => $task,
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => "this task doesn't exist.",

            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified task in storage if you are autherized to edit.
     *
     * @param  \App\Http\Requests\UpdateTaskRequest  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $projectId = Task::Where('id', $task->id)->first()->projectId;
        $projectSupervisorId = Project::findOrFail($projectId)->supervisorId;

        if ($projectSupervisorId != NULL && $projectSupervisorId == auth()->id()) {
            $task->update($request->all());

            return response()->json([
                'status' => true,
                'message' => "Task Updated successfully!",
                'project' => $task,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "you are not authorize to edit",
            ], 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        $projectId = Task::Where('id', $task->id)->first()->projectId;
        $projectSupervisorId = Project::findOrFail($projectId)->supervisorId;

        if ($projectSupervisorId != NULL && $projectSupervisorId == auth()->id()) {
            $task->delete();
            return response()->json([
                'status' => true,
                'message' => "Task Deleted successfully!",
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "you are not authorize to edit",
            ], 403);
        }
    }

    /**
     * assignEmployee to task  if you are autherized to edit.
     *
     * @param  \App\Http\Requests\AssignEmployeeRequest  $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function assignEmployee(AssignEmployeeRequest $request)
    {
        $taskId = $request->taskId;
        $employeeId = $request->employeeId;

        $projectId = Task::Where('id', $taskId)->first()->projectId;

        $projectSupervisorId = Project::where('id', $projectId)->first()->supervisorId;

        $isId = User::Where('id', $employeeId)->where('supervisorId', $projectSupervisorId)->get();
        if (count($isId) == 0) {
            return response()->json([
                'status' => false,
                'message' => "unvalid employee can't find employee with id =$employeeId",
                'sd' => $isId,
                'ewged' => $projectSupervisorId,
                'sdg' => $projectId,
                'task' => $taskId
            ], 400);
        }

        if ($projectSupervisorId == auth()->id()) {
            $isCompleted = Task::where('id', $taskId)->first()->isCompleted;
            if ($isCompleted == 1) {
                return response()->json([
                    'status' => false,
                    'message' => "can't update completed tasks",

                ]);
            }
            Task::where('id', $taskId)->update(array('employeeId' => $employeeId));
            return response()->json([
                'status' => true,
                'message' => "employee assigned to Task successfully!",

            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "you are not authorized to add employee!",

            ],  401);
        }
    }

    /**
     * CompleteTaskRequest task to be done if you are autherized to submit.
     *
     * @param  \App\Http\Requests\CompleteTaskRequest  $request
     * 
     * @return \Illuminate\Http\Response
     */

    public function completeTask(CompleteTaskRequest $request)
    {
        $taskId = $request->taskId;

        $employeeId = Task::Where('id', '=', $taskId)->first()->employeeId;


        if ($employeeId == auth()->id()) {
            $isCompleted = Task::where('id', $taskId)->first()->isCompleted;
            if ($isCompleted == 1) {
                return response()->json([
                    'status' => false,
                    'message' => "Task is already completed",
                ]);
            }
            Task::where('id', $taskId)->update(array('isCompleted' => 1));
            $projectId = Task::Where('id', $taskId)->first()->projectId;
            $project = Project::Where('id', $projectId)->first();


            Project::where('id', $project->id)->update(array('numberOfCompletedTasks' => $project->numberOfCompletedTasks + 1));

            return response()->json([
                'status' => true,
                'message' => "Task is completed successfully!",

            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "you are not authorized to edit this task!",
            ],  401);
        }
    }
}
