<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the projects to user who logged in.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Project::where('supervisorId', auth()->id())->get();
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
     * Store a newly created project in storage.
     *
     * @param  \App\Http\Requests\StoreProjectRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProjectRequest $request)
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
            'requist' => $request->name
        ], 200);
    }

    /**
     * Display the specified project to user who logged in.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
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
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified project in storage.
     *
     * @param  \App\Http\Requests\UpdateProjectRequest  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        if (!($project->supervisorId == auth()->id())) {
            return response()->json([
                'status' => False,
                'message' => "permission is dennied",
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
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        if (!($project->supervisorId == auth()->id())) {
            return response()->json([
                'status' => False,
                'message' => "permission is dennied",
            ], 403);
        } else {
            if (count(Task::where('projectId', $project->id)->where('isCompleted', 1)->get()) != 0) {
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
