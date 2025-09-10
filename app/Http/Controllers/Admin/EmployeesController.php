<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Models\Task;
use App\Models\Timesheet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = Employee::latest()->get();

        return view('admin.employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employee = new Employee();
        return view('admin.employees.create', compact('employee'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EmployeeRequest $request)
    {
        $data = $request->validated();

        unset($data['user']);

        // إنشاء الموظف بدون user_id
        $employee = Employee::create($data);

        // معلومات التواصل
        $employee->contacts()->create([
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
        ]);

        // إنشاء مستخدم في حال تم إدخال بيانات
        if ($request->filled('user.email') && $request->filled('user.password')) {
            $user = User::create([
                'name' => $request->input('user.name'),
                'email' => $request->input('user.email'),
                'password' => bcrypt($request->input('user.password')),
                'type' => 'employee',
                'is_marketer' => $request->input('user.is_marketer') == '1', // إضافة حالة التسويق
                'is_accountant' => $request->input('user.is_accountant') == '1', // إضافة حالة المحاسبة
            ]);

            $employee->user()->associate($user);
            $employee->save();
        }

        flash()->success('Employee created successfully');
        return redirect()->route('admin.employees.index');
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
    public function edit(Employee $employee)
    {
        $employee->load('user', 'contacts'); // تحميل بيانات المستخدم وجهات الاتصال
        return view('admin.employees.edit', compact('employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmployeeRequest $request, Employee $employee)
    {
        $data = $request->except(['user', 'phone', 'email', 'address']);
        // 🚫 لا نضع user_id هنا لأنه ليس من المفترض أن يتغير إلا في حالة إنشاء مستخدم جديد
        // $data['user_id'] = Auth::id(); // احذف هذا السطر

        // 检查是否更改了账户信息字段
        $accountFieldsChanged = false;
        $accountFields = ['account_name', 'account_number', 'iban', 'bank_code'];

        foreach ($accountFields as $field) {
            if ($request->has($field) && $request->input($field) !== $employee->$field) {
                $accountFieldsChanged = true;
                break;
            }
        }

        // 如果账户信息有更改，将 is_iban_valid 设置为 0
        if ($accountFieldsChanged) {
            $data['is_iban_valid'] = 0;
        }

        // تحديث بيانات الموظف
        $employee->update($data);

        // تحديث أو إنشاء جهة الاتصال
        $contact = $employee->contacts->first();
        if ($contact) {
            $contact->update([
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
            ]);
        } else {
            $employee->contacts()->create([
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
            ]);
        }

        // تحديث أو إنشاء حساب المستخدم المرتبط
        if ($request->filled('user.email')) {
            if ($employee->user) {
                // تحديث المستخدم الموجود
                $updateData = [
                    'name' => $request->input('user.name'),
                    'email' => $request->input('user.email'),
                    'is_marketer' => $request->input('user.is_marketer') == '1',
                    'is_accountant' => $request->input('user.is_accountant') == '1', // إضافة حالة المحاسبة
                ];

                if ($request->filled('user.password')) {
                    $updateData['password'] = bcrypt($request->input('user.password'));
                }

                $employee->user->update($updateData);
            } else {
                // إنشاء مستخدم جديد فقط إذا تم إدخال كلمة المرور
                if ($request->filled('user.password')) {
                    $user = User::create([
                        'name' => $request->input('user.name'),
                        'email' => $request->input('user.email'),
                        'password' => bcrypt($request->input('user.password')),
                        'type' => 'employee',
                        'is_marketer' => $request->input('user.is_marketer') == '1',
                        'is_accountant' => $request->input('user.is_accountant') == '1', // إضافة حالة المحاسبة
                    ]);

                    // ربط المستخدم بالموظف
                    $employee->user()->associate($user);
                    $employee->save();
                }
            }
        }
        // ✳️ لا تفعل أي شيء في حالة عدم وجود بيانات مستخدم

        flash()->success('Employee updated successfully');
        return redirect()->route('admin.employees.index');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        if ($employee->user) {
            $employee->user->delete();
        }

        $employee->delete();

        flash()->success('Employee deleted successfully');
        return redirect()->route('admin.employees.index');
    }

    public function projects($employeeId)
    {
        $employee = Employee::with('projects')->findOrFail($employeeId);

        $projects = $employee->projects->map(function ($project) {
            return [
                'id' => $project->id,
                'name' => $project->name,
            ];
        });

        return response()->json($projects);
    }

    /**
     * Toggle banned status for the employee user.
     */
    public function toggleBan(Employee $employee)
    {
        if (!$employee->user) {
            flash()->error('This employee does not have an associated user account.');
            return redirect()->route('admin.employees.index');
        }

        $employee->user->banned = !$employee->user->banned;
        $employee->user->save();

        $status = $employee->user->banned ? 'banned' : 'unbanned';
        flash()->success("Employee has been {$status} successfully.");
        return redirect()->route('admin.employees.index');
    }

    /**
     * Display the timesheet for a specific employee.
     */
    public function timesheet(Employee $employee, Request $request)
    {
        $monthFilter = $request->input('month', 'all');
        $projectFilter = $request->input('project_id', 'all');

        $month = null;
        $monthStart = null;
        $monthEnd = null;

        // Get the employee's timesheet for the selected month
        if ($monthFilter !== 'all') {
            $month = Carbon::parse($monthFilter);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
        }

        // Find or create a timesheet for this employee for the selected month
        if ($monthFilter === 'all') {
            // When "all months" is selected, calculate totals across all months
            if ($projectFilter !== 'all') {
                // If project filter is applied, calculate statistics from tasks instead of timesheets
                $filteredTasks = Task::where('employee_id', $employee->id)
                    ->where('project_id', $projectFilter)
                    ->get();

                // Create a summary timesheet object based on filtered tasks
                $timesheet = new Timesheet();
                $timesheet->employee_id = $employee->id;
                $timesheet->work_date = now(); // Current date as reference
                $timesheet->hours_worked = $filteredTasks->sum('duration_in_hours');
                $timesheet->month_salary = $filteredTasks->sum('duration_in_hours') * $employee->rate;
                $timesheet->is_paid = false;
                $timesheet->rate = $employee->rate;
            } else {
                // No project filter, use all timesheets
                $allTimesheets = Timesheet::where('employee_id', $employee->id)->get();

                // Create a summary timesheet object
                $timesheet = new Timesheet();
                $timesheet->employee_id = $employee->id;
                $timesheet->work_date = now(); // Current date as reference
                $timesheet->hours_worked = $allTimesheets->sum('hours_worked');
                $timesheet->month_salary = $allTimesheets->sum('month_salary');
                $timesheet->is_paid = false;

                // If no timesheets exist, set default values
                if ($allTimesheets->isEmpty()) {
                    $timesheet->hours_worked = 0;
                    $timesheet->month_salary = 0;
                }
            }
        } else {
            // For a specific month
            if ($projectFilter !== 'all') {
                // If project filter is applied, calculate statistics from tasks instead of timesheets
                $filteredTasks = Task::where('employee_id', $employee->id)
                    ->where('project_id', $projectFilter)
                    ->whereBetween('start_time', [$monthStart, $monthEnd])
                    ->get();

                // Create a summary timesheet object based on filtered tasks
                $timesheet = new Timesheet();
                $timesheet->employee_id = $employee->id;
                $timesheet->work_date = $monthStart;
                $timesheet->hours_worked = $filteredTasks->sum('duration_in_hours');
                $timesheet->month_salary = $filteredTasks->sum('duration_in_hours') * $employee->rate;
                $timesheet->is_paid = false;
                $timesheet->rate = $employee->rate;
            } else {
                // No project filter, use the regular timesheet
                $timesheetQuery = Timesheet::where('employee_id', $employee->id)
                    ->whereYear('work_date', $month->year)
                    ->whereMonth('work_date', $month->month);

                $timesheet = $timesheetQuery->first();

                if (!$timesheet) {
                    // Create a new timesheet if it doesn't exist
                    $timesheet = new Timesheet();
                    $timesheet->employee_id = $employee->id;
                    $timesheet->work_date = $monthStart;
                    $timesheet->hours_worked = 0;
                    $timesheet->month_salary = 0;
                    $timesheet->is_paid = false;
                    $timesheet->save();
                }
            }
        }

        // Get tasks for this employee in the selected month
        $tasksQuery = Task::where('employee_id', $employee->id)->with('project');

        if ($month) {
            $tasksQuery->whereBetween('start_time', [$monthStart, $monthEnd]);
        }

        if ($projectFilter !== 'all') {
            $tasksQuery->where('project_id', $projectFilter);
        }

        $tasks = $tasksQuery->get();

        // Get available months for this employee
        $availableMonths = Task::where('employee_id', $employee->id)
            ->selectRaw('DATE_FORMAT(start_time, "%Y-%m") as value, DATE_FORMAT(start_time, "%M %Y") as label')
            ->groupBy('value', 'label')
            ->orderBy('value', 'desc')
            ->get()
            ->toArray();

        // Get employee's projects for filter
        $projects = $employee->projects;

        return view('admin.employees.timesheet', compact(
            'employee',
            'timesheet',
            'tasks',
            'monthFilter',
            'projectFilter',
            'availableMonths',
            'projects'
        ));
    }
}
