<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimesheetRequest;
use App\Models\Employee;
use App\Models\Timesheet;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimesheetsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $timesheets = Timesheet::all();

        return view('admin.timesheets.index', compact('timesheets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $timesheet = new Timesheet();
        $employees = Employee::all();
        $projects = Project::all();

        return view('admin.timesheets.create', compact('timesheet', 'projects', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TimesheetRequest $request)
    {
        $data = $request->validated();
        $data['work_date'] = $request->work_date;
        $data['hours_worked'] = $request->hours_worked;
        $data['employee_id'] = $request->employee_id;
        $data['project_id'] = $request->project_id;
        Timesheet::create($data);

        flash()->success('Timesheet created successfully');
        return redirect()->route('admin.timesheets.index');
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
    public function edit(Timesheet $timesheet)
    {
        $employees = Employee::all();
        $projects = Project::all();
        return view('admin.timesheets.edit', compact('timesheet', 'projects', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TimesheetRequest $request, Timesheet $timesheet)
    {
        $data = $request->validated();
        $data['employee_id'] = $request->employee_id;
        $data['project_id'] = $request->project_id;
        $timesheet->update($data);

        flash()->success('Timesheet updated successfully');
        return redirect()->route('admin.timesheets.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Timesheet $timesheet)
    {
        $timesheet->delete();

        flash()->success('Timesheet deleted successfully');
        return redirect()->route('admin.timesheets.index');
    }
}
