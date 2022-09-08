<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    /**
     * Display a listing of the projects to user who logged in.
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
//        $projects = Project::with('tasks')->get();
//        return response()->json([
//            'status' => true,
//            'message' => "Post Created successfully!",
//             'project' => $projects->pluck("tasks",'id'),
//        ], 200);
        return Project::where('supervisorId', auth()->id())->get();
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
     * Store a newly created project in storage.
     *
     * @param StoreProjectRequest $request
     * @return JsonResponse
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $Project = Project::create([
            'name' => $request->name,
            'complexity' => $request->complexity,
            'deadLine' => $request->deadLine,
            'description' => $request->description,
            'supervisorId' => auth()->id()
        ]);
        return response()->json([
            'status' => true,
            'message' => "Post Created successfully!",
            'project' => $Project,
        ], 200);
    }

    /**
     * Display the specified project to user who logged in.
     *
     * @param Project $project
     * @return Project|JsonResponse
     */
    public function show(Project $project): Project|JsonResponse
    {
        if (!($project->supervisorId == auth()->id())) {
            return response()->json([
                'status' => False,
                'message' => "can't find project",
            ], 404);
        } else {
            return $project;
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Project $project
     * @return Response
     */
//    public function edit(Project $project)
//    {
//        //
//    }

    /**
     * Update the specified project in storage.
     *
     * @param UpdateProjectRequest $request
     * @param Project $project
     * @return JsonResponse
     */
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        if (!($project->supervisorId == auth()->id())) {
            return response()->json([
                'status' => False,
                'message' => "permission is denied",
            ], 403);
        } else {
            $project->update($request->all());

            return response()->json([
                'status' => true,
                'message' => "project Updated successfully!",
                'project' => $project,
            ], 200);
        }
    }

    /**
     * Remove the specified project but all tasks in it must be completed from storage.
     *
     * @param Project $project
     * @return JsonResponse
     */
    public function destroy(Project $project): JsonResponse
    {
        if (!($project->supervisorId == auth()->id())) {
            return response()->json([
                'status' => False,
                'message' => "permission is denied",
            ], 403);
        } else {
            if (count(Task::where('project_id', $project->id)->where('isCompleted', 1)->get()) != 0) {
                $project->delete();
                return response()->json([
                    'status' => true,
                    'message' => "project Deleted successfully!",
                    'project' => $project
                ], 200);
            } else {
                return response()->json([
                    'status' => False,
                    'message' => "project has some tasks that is not completed",
                ]);
            }
        }
    }
}
