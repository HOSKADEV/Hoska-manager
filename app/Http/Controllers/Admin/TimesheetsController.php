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

class TimesheetsController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        // بناء قائمة الأشهر مع خيار "All" يدويًا
        $availableMonths = Timesheet::selectRaw('DATE_FORMAT(work_date, "%Y-%m") as value, DATE_FORMAT(work_date, "%M %Y") as label')
            ->groupBy('value', 'label')
            ->orderBy('value', 'desc')
            ->get()
            ->toArray();

        // افتراضيًا، الشهر الحالي كقيمة فلتر إذا ما حدد المستخدم شيء
        $monthFilter = $request->input('month', now()->format('Y-m'));

        $query = Timesheet::query();

        if ($monthFilter && $monthFilter !== 'all') {
            $month = Carbon::parse($monthFilter);
            $query->whereMonth('work_date', $month->month)
                ->whereYear('work_date', $month->year);

            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
        } else {
            // لو اختار "all" (يعني عرض الكل)
            $monthStart = Timesheet::min('work_date') ?? now();
            $monthEnd = Timesheet::max('work_date') ?? now();
        }

        $timesheets = $query->with('employee', 'project')->get();

        // احسب الاحصائيات ضمن الفترة المختارة (أو الكل)
        $totalHours = Timesheet::whereBetween('work_date', [$monthStart, $monthEnd])->sum('hours_worked');
        $totalSalaries = Timesheet::whereBetween('work_date', [$monthStart, $monthEnd])->sum('month_salary');
        $paidCount = Timesheet::whereBetween('work_date', [$monthStart, $monthEnd])->where('is_paid', true)->count();
        $unpaidCount = Timesheet::whereBetween('work_date', [$monthStart, $monthEnd])->where('is_paid', false)->count();

        return view('admin.timesheets.index', compact(
            'timesheets',
            'availableMonths',
            'totalHours',
            'totalSalaries',
            'paidCount',
            'unpaidCount',
            'monthFilter'
        ));
    }


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
