<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimesheetRequest;
use App\Models\Employee;
use App\Models\Timesheet;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TimesheetExport;

class TimesheetsController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $availableMonths = Timesheet::selectRaw('DATE_FORMAT(work_date, "%Y-%m") as value, DATE_FORMAT(work_date, "%M %Y") as label')
            ->groupBy('value', 'label')
            ->orderBy('value', 'desc')
            ->get()
            ->toArray();

        $monthFilter = $request->input('month', now()->format('Y-m'));
        $isPaidFilter = $request->input('is_paid', 'all'); // الافتراضي 'all'

        $query = Timesheet::query();

        if ($monthFilter && $monthFilter !== 'all') {
            $month = Carbon::parse($monthFilter);
            $query->whereMonth('work_date', $month->month)
                ->whereYear('work_date', $month->year);

            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
        } else {
            $monthStart = Timesheet::min('work_date') ?? now();
            $monthEnd = Timesheet::max('work_date') ?? now();
        }

        // طبّق فلتر الدفع فقط إذا القيمة ليست 'all'
        if ($isPaidFilter !== 'all') {
            $query->where('is_paid', $isPaidFilter);
        }

        $timesheets = $query->with('employee', 'project')->get();

        // إحصائيات ضمن فترة الشهر المختار
        $statsQuery = Timesheet::whereBetween('work_date', [$monthStart, $monthEnd]);

        // طبّق فلتر الدفع على الإحصائيات أيضاً بنفس المنطق
        if ($isPaidFilter !== 'all') {
            $statsQuery->where('is_paid', $isPaidFilter);
        }

        $totalHours = (clone $statsQuery)->sum('hours_worked');
        $totalSalaries = (clone $statsQuery)->sum('month_salary');

        if ($isPaidFilter === '1') {
            $paidCount = (clone $statsQuery)->count();
            $unpaidCount = 0;
        } elseif ($isPaidFilter === '0') {
            $paidCount = 0;
            $unpaidCount = (clone $statsQuery)->count();
        } else {
            $paidCount = (clone $statsQuery)->where('is_paid', true)->count();
            $unpaidCount = (clone $statsQuery)->where('is_paid', false)->count();
        }

        return view('admin.timesheets.index', compact(
            'timesheets',
            'availableMonths',
            'totalHours',
            'totalSalaries',
            'paidCount',
            'unpaidCount',
            'monthFilter',
            'isPaidFilter'
        ));
    }



    public function markPaid($id)
    {
        $timesheet = Timesheet::findOrFail($id);
        $timesheet->is_paid = true;
        $timesheet->save();
        flash()->success('Timesheet marked as paid successfully.');
        return redirect()->back();
    }

    // public function export(Request $request)
    // {
    //     $columns = $request->get('columns', []);

    //     if (empty($columns)) {
    //         return back()->with('error', 'Please select at least one column.');
    //     }

    //     // return Excel::download(new TimesheetExport($columns), 'timesheets_' . now()->format('Y_m') . '.xlsx');
    // }

    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     $timesheet = new Timesheet();
    //     $employees = Employee::all();
    //     $projects = Project::all();

    //     return view('admin.timesheets.create', compact('timesheet', 'projects', 'employees'));
    // }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(TimesheetRequest $request)
    // {
    //     $data = $request->validated();
    //     $data['work_date'] = $request->work_date;
    //     // $data['hours_worked'] = $request->hours_worked;
    //     $data['employee_id'] = $request->employee_id;
    //     // $data['project_id'] = $request->project_id;

    //     // استخراج المشاريع المرتبطة بالموظف
    //     $employee = Employee::with('projects', 'tasks')->findOrFail($request->employee_id);

    //     // تحقق من وجود مشاريع مرتبطة
    //     if ($employee->projects->isEmpty()) {
    //         return back()->withErrors(['employee_id' => 'هذا الموظف لا يحتوي على مشروع مرتبط.']);
    //     }

    //     // إذا كنت تريد أول مشروع فقط
    //     $data['project_id'] = $employee->projects->first()->id;

    //     // حساب مجموع ساعات العمل من المهام - وتخزينها في الحقل hours_worked تلقائياً
    //     $data['hours_worked'] = $employee->tasks->sum('duration_in_hours');

    //     Timesheet::create($data);

    //     flash()->success('Timesheet created successfully');
    //     return redirect()->route('admin.timesheets.index');
    // }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $timesheet = Timesheet::findOrFail($id);

        $employee = Employee::findOrFail($timesheet->employee_id);

        // تحديد بداية ونهاية الشهر المرتبط بالتايم شيت
        $monthStart = Carbon::parse($timesheet->work_date)->startOfMonth();
        $monthEnd = Carbon::parse($timesheet->work_date)->endOfMonth();

        // جلب المهام ضمن نفس الشهر ونفس الموظف
        $tasks = Task::where('employee_id', $employee->id)
            ->whereDate('start_time', '>=', $monthStart)
            ->whereDate('start_time', '<=', $monthEnd)
            ->with('project')
            ->get();

        return view('admin.timesheets.show', compact('timesheet', 'employee', 'tasks'));
    }
    /**
     * Show the form for editing the specified resource.
     */
    // public function edit(Timesheet $timesheet)
    // {
    //     $employees = Employee::all();
    //     $projects = Project::all();
    //     return view('admin.timesheets.edit', compact('timesheet', 'projects', 'employees'));
    // }

    /**
     * Update the specified resource in storage.
     */
    // public function update(TimesheetRequest $request, Timesheet $timesheet)
    // {
    //     $data = $request->validated();
    //     $data['employee_id'] = $request->employee_id;

    //     // جلب الموظف مع المشاريع والمهام معًا
    //     $employee = Employee::with(['projects', 'tasks'])->findOrFail($request->employee_id);

    //     if ($employee->projects->isEmpty()) {
    //         return back()->withErrors(['employee_id' => 'هذا الموظف لا يحتوي على مشروع مرتبط.']);
    //     }

    //     $data['project_id'] = $employee->projects->first()->id;

    //     // حساب مجموع ساعات العمل من المهام
    //     $data['hours_worked'] = $employee->tasks->sum('duration_in_hours');

    //     $timesheet->update($data);

    //     flash()->success('Timesheet updated successfully');
    //     return redirect()->route('admin.timesheets.index');
    // }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(Timesheet $timesheet)
    // {
    //     $timesheet->delete();

    //     flash()->success('Timesheet deleted successfully');
    //     return redirect()->route('admin.timesheets.index');
    // }
}
