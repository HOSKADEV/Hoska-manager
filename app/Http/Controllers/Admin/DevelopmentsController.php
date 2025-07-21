<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DevelopmentRequest;
use App\Models\Development;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DevelopmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $developments = Development::all();

        return view('admin.developments.index', compact('developments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $development = new Development();
        $projects = Project::all();

        return view('admin.developments.create', compact('development', 'projects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DevelopmentRequest $request)
    {
        $data = $request->validated();
        $data['project_id'] = $request->project_id;
        Development::create($data);

        flash()->success('Development created successfully');
        return redirect()->route('admin.developments.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Development $development)
    {
        $projects = Project::all();
        return view('admin.developments.edit', compact('development', 'projects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DevelopmentRequest $request, Development $development)
    {
        $data = $request->validated();
        $data['project_id'] = $request->project_id;

        if (isset($data['duration_days'])) {
            $data['duration_days'] = (int) $data['duration_days'];
        }

        $development->update($data);

        flash()->success('Development updated successfully');
        return redirect()->route('admin.developments.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Development $development)
    {
        $development->delete();

        flash()->success('Development deleted successfully');
        return redirect()->route('admin.developments.index');
    }
}
