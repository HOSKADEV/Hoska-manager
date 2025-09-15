<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;
use App\Models\Timesheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamManagerProjectsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check if user is an employee and has team manager role
        if ($user->type !== 'employee' || !$user->employee) {
            abort(403, 'Unauthorized access');
        }

        $employeeId = $user->employee->id;

        // Get projects where this employee is the team manager
        $projects = Project::where('team_manager_id', $employeeId)
            ->with('employees', 'client')
            ->orderBy('start_date', 'desc')
            ->get();

        return view('admin.team-manager-projects.index', compact('projects'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $user = Auth::user();

        // Check if user is an employee and is the team manager of this project
        if ($user->type !== 'employee' || !$user->employee || $project->team_manager_id !== $user->employee->id) {
            abort(403, 'Unauthorized access');
        }

        $project->load('employees', 'client', 'tasks', 'timesheets');

        return view('admin.team-manager-projects.show', compact('project'));
    }

    /**
     * Show timesheets for a project filtered by employee
     */
    public function timesheets(Project $project, Request $request)
    {
        $user = Auth::user();

        // Check if user is an employee and is the team manager of this project
        if ($user->type !== 'employee' || !$user->employee || $project->team_manager_id !== $user->employee->id) {
            abort(403, 'Unauthorized access');
        }

        $employeeFilter = $request->input('employee_id', 'all');
        $monthFilter = $request->input('month', now()->format('Y-m'));

        $query = Timesheet::where('project_id', $project->id);

        if ($employeeFilter !== 'all') {
            $query->where('employee_id', $employeeFilter);
        }

        if ($monthFilter !== 'all') {
            $query->whereRaw('DATE_FORMAT(work_date, "%Y-%m") = ?', [$monthFilter]);
        }

        $timesheets = $query->with('employee')->get();

        // Get project employees for filter
        $employees = $project->employees;

        return view('admin.team-manager-projects.timesheets', compact(
            'project', 
            'timesheets', 
            'employees', 
            'employeeFilter', 
            'monthFilter'
        ));
    }

    /**
     * Show tasks for a project filtered by employee
     */
    public function tasks(Project $project, Request $request)
    {
        $user = Auth::user();

        // Check if user is an employee and is the team manager of this project
        if ($user->type !== 'employee' || !$user->employee || $project->team_manager_id !== $user->employee->id) {
            abort(403, 'Unauthorized access');
        }

        $employeeFilter = $request->input('employee_id', 'all');
        $statusFilter = $request->input('status', 'all');

        $query = Task::where('project_id', $project->id);

        if ($employeeFilter !== 'all') {
            $query->where('employee_id', $employeeFilter);
        }

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $tasks = $query->with('employee')->latest()->get();

        // Get project employees for filter
        $employees = $project->employees;

        return view('admin.team-manager-projects.tasks', compact(
            'project', 
            'tasks', 
            'employees', 
            'employeeFilter', 
            'statusFilter'
        ));
    }
}
