<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OurTaskRequest;
use App\Models\OurTask;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OurTasksController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(OurTaskRequest $request)
    {
        $data = $request->validated();

        $task = OurTask::create($data);

        flash()->success('Task created successfully');
        return response()->json($task);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = OurTask::with([
            'project.client',
        ])->findOrFail($id);

        return response()->json($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OurTaskRequest $request,$id)
    {
        $data = $request->validated();
        $task = OurTask::find($id);
        $task->update($data);

        flash()->success('Task updated successfully');
        return response()->json($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $task = OurTask::find($id);
            $task->delete();
            flash()->success('Task deleted successfully');

            return response()->json([
                'id' => $task,
                'success' => true,
                'message' => 'Task deleted successfully'
            ]);
        } catch (\Exception $e) {
            flash()->error('Task error on deleted');

            return response()->json([
                'success' => false,
                'message' => 'Error deleting task: ' . $e->getMessage()
            ], 500);
        }
    }
}
