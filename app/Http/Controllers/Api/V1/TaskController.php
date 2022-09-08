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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;


class TaskController extends Controller
{
    /**
     * Display a listing of the tasks to authenticated user that is supervisor in or employee.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $userId = auth()->id();
        //tasks as supervisor
        $projectsAsSupervisor = Project::where("supervisorId",  $userId)->get();
        $tasks = [];
        $counter = 0;

        foreach ($projectsAsSupervisor as $project) {
            $task = Task::where('project_id', $project->id)->get();
            $tasks[$counter] = $task;
            $counter++;
        }
        //tasks as employee

        $employees =  User::where("id",  $userId)->pluck('supervisorId');
        foreach ($employees  as $employee) {
            $projects = Project::where("supervisorId",  $employee)->get();
            foreach ($projects as $project) {
                $task = Task::where('project_id', $project->id)->get();
                $tasks[$counter] = $task;
                $counter++;
            }
        }
        if ($counter == 0) {
            return response()->json([
                'status' => true,
                'message' => 'you have no tasks',
            ]);
        } else {
            return response()->json([
                'status' => true,
                'tasks' => $tasks,
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
//    public function create()
//    {
//        //
//    }

    /**
     * Store a newly created task in storage.
     *
     * @param StoreTaskRequest $request
     * @return JsonResponse
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $projectId = $request->projectId;
        $projectSupervisorId = Project::findOrFail($projectId)->supervisorId;

        if ($projectSupervisorId != NULL && $projectSupervisorId == auth()->id()) {
            $Project = Task::create([
                'name' => $request->name,
                // 'complexity' => $request->complexity,
                'description' => $request->description,
                'employeeId' => NULL,
                'project_id' => $request->projectId,
            ]);
            return response()->json([
                'status' => true,
                'message' => "Project Created successfully!",
                'Project' => $Project,
            ]);
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
     * @return JsonResponse
     */
    public function projectTasks(): JsonResponse
    {
        $id = $_GET['id'];

        if (count(Task::where("project_id",  $id)->get())) {
            $tasks = Task::where('project_id', '=', $id)->get();
            return response()->json([
                'status' => true,
                'Project' => $tasks,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Project has no tasks",
            ]);
        }
    }

    /**
     * Display the specified specific task if you are authorized to get.
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function show(Task $task): JsonResponse
    {
        $taskId = $task->id;
        $projectId = Task::Where('id', $taskId)->first()->project_id;
        if ($projectId) {
            $userId = auth()->id();

            $projectSupervisorId = Project::where('id', $projectId)->first()->supervisorId;

            if ($projectSupervisorId == $userId || count(User::where('id', $userId)->where("supervisorId",  $projectSupervisorId)->get())) {
                $task = Task::where('id', $taskId)->first();
                return response()->json([
                    'status' => true,
                    'Project' => $task,
                ]);
            }
        }
        return response()->json([
            'status' => false,
            'message' => "this task doesn't exist.",

        ], 404);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Task $task
     * @return Response
     */
//    public function edit(Task $task)
//    {
//        //
//    }

    /**
     * Update the specified task in storage if you are authorized to edit.
     *
     * @param UpdateTaskRequest $request
     * @param Task $task
     * @return JsonResponse
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $projectId = Task::Where('id', $task->id)->first()->project_id;
        $projectSupervisorId = Project::findOrFail($projectId)->supervisorId;

        if ($projectSupervisorId != NULL && $projectSupervisorId == auth()->id()) {
            $task->update($request->all());

            return response()->json([
                'status' => true,
                'message' => "Task Updated successfully!",
                'project' => $task,
            ]);
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
     * @param Task $task
     * @return JsonResponse
     */
    public function destroy(Task $task): JsonResponse
    {
        $projectId = Task::Where('id', $task->id)->first()->project_id;
        $projectSupervisorId = Project::findOrFail($projectId)->supervisorId;

        if ($projectSupervisorId != NULL && $projectSupervisorId == auth()->id()) {
            $task->delete();
            return response()->json([
                'status' => true,
                'message' => "Task Deleted successfully!",
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => "you are not authorize to edit",
            ], 403);
        }
    }

    /**
     * assignEmployee to task  if you are authorized to edit.
     *
     * @param AssignEmployeeRequest $request
     *
     * @return JsonResponse
     */
    public function assignEmployee(AssignEmployeeRequest $request): JsonResponse
    {
        $taskId = $request->taskId;
        $employeeId = $request->employeeId;

        $projectId = Task::Where('id', $taskId)->first()->project_id;

        $projectSupervisorId = Project::where('id', $projectId)->first()->supervisorId;

        $isId = User::Where('id', $employeeId)->where('supervisorId', $projectSupervisorId)->get();
        if (count($isId) == 0) {
            return response()->json([
                'status' => false,
                'message' => "invalid employee can't find employee with id =$employeeId",
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
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => "you are not authorized to add employee!",

            ],  401);
        }
    }

    /**
     * CompleteTaskRequest task to be done if you are authorized to submit.
     *
     * @param CompleteTaskRequest $request
     *
     * @return JsonResponse
     */

    public function completeTask(CompleteTaskRequest $request): JsonResponse
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
            $projectId = Task::Where('id', $taskId)->first()->project_id;
            $project = Project::Where('id', $projectId)->first();


            Project::where('id', $project->id)->update(array('numberOfCompletedTasks' => $project->numberOfCompletedTasks + 1));

            return response()->json([
                'status' => true,
                'message' => "Task is completed successfully!",

            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => "you are not authorized to edit this task!",
            ],  401);
        }
    }
}
