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

        // Get tasks for this project
        $query = Task::where('project_id', $project->id);

        // Group by employee and month to get total hours
        $timesheets = $query->with('employee')
            ->selectRaw('employee_id, DATE_FORMAT(start_time, "%Y-%m") as month, SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time) / 60) as total_hours')
            ->groupBy('employee_id', 'month')
            ->get();

        $project->load('employees', 'client', 'tasks');

        return view('admin.team-manager-projects.show', compact('project', 'timesheets'));
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

        // Get tasks for this project
        $query = Task::where('project_id', $project->id);

        if ($employeeFilter !== 'all') {
            $query->where('employee_id', $employeeFilter);
        }

        if ($monthFilter !== 'all') {
            $query->whereRaw('DATE_FORMAT(start_time, "%Y-%m") = ?', [$monthFilter]);
        }

        // Group by employee and month to get total hours
        $timesheets = $query->with('employee')
            ->selectRaw('employee_id, DATE_FORMAT(start_time, "%Y-%m") as month, SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time) / 60) as total_hours')
            ->groupBy('employee_id', 'month')
            ->get();

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
     * Show detailed timesheets for a specific employee and month
     */
    public function timesheetDetails(Project $project, Request $request)
    {
        $user = Auth::user();

        // Check if user is an employee and is the team manager of this project
        if ($user->type !== 'employee' || !$user->employee || $project->team_manager_id !== $user->employee->id) {
            abort(403, 'Unauthorized access');
        }

        $employeeId = $request->input('employee_id');
        $month = $request->input('month');

        if (!$employeeId || !$month) {
            return redirect()->route('admin.team-manager-projects.timesheets', $project->id)
                ->with('error', 'Please select both employee and month');
        }

        // Get detailed tasks for the selected employee and month
        $tasks = Task::where('project_id', $project->id)
            ->where('employee_id', $employeeId)
            ->whereRaw('DATE_FORMAT(start_time, "%Y-%m") = ?', [$month])
            ->with('employee')
            ->orderBy('start_time')
            ->get();

        $employee = Employee::find($employeeId);

        return view('admin.team-manager-projects.timesheet-details', compact(
            'project',
            'tasks',
            'employee',
            'month'
        ));
    }

    /**
     * Export timesheets for a project
     */
    public function exportTimesheets(Project $project, Request $request)
    {
        $user = Auth::user();

        // Check if user is an employee and is the team manager of this project
        if ($user->type !== 'employee' || !$user->employee || $project->team_manager_id !== $user->employee->id) {
            abort(403, 'Unauthorized access');
        }

        $employeeFilter = $request->input('employee_id', 'all');
        $monthFilter = $request->input('month', now()->format('Y-m'));

        // Get tasks for this project
        $query = Task::where('project_id', $project->id);

        if ($employeeFilter !== 'all') {
            $query->where('employee_id', $employeeFilter);
        }

        if ($monthFilter !== 'all') {
            $query->whereRaw('DATE_FORMAT(start_time, "%Y-%m") = ?', [$monthFilter]);
        }

        $tasks = $query->with('employee')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=tasks_' . $project->id . '_' . $monthFilter . '.csv',
        ];

        $callback = function() use ($tasks) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, ['Employee', 'Start Time', 'End Time', 'Task', 'Hours Worked']);

            // Add data rows
            foreach ($tasks as $task) {
                fputcsv($file, [
                    $task->employee->name ?? 'N/A',
                    $task->start_time,
                    $task->end_time,
                    $task->name,
                    $task->duration_in_hours
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
