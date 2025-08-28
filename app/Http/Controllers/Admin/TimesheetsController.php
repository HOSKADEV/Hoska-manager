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
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

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

        $timesheets = $query->with('employee', 'project')->latest()->get();

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

        $columns = Schema::getColumnListing('timesheets');

        // ✅ مجموع الرواتب حسب العملة من جدول الموظفين
        $salariesByCurrency = [];

        foreach ((clone $statsQuery)->with('employee')->get() as $timesheet) {
            $currency = $timesheet->employee->currency ?? 'UNKNOWN';
            $salariesByCurrency[$currency] = ($salariesByCurrency[$currency] ?? 0) + $timesheet->month_salary;
        }

        return view('admin.timesheets.index', compact(
            'timesheets',
            'availableMonths',
            'totalHours',
            'totalSalaries',
            'paidCount',
            'unpaidCount',
            'monthFilter',
            'isPaidFilter',
            'columns',
            'salariesByCurrency' // أضفنا هذا المتغير الجديد
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

    /**
     * Export selected columns of timesheets for a specific month.
     */
    public function exportSelectedColumns(Request $request)
    {
        $columns = $request->input('columns', []);
        $month = $request->input('month', now()->format('Y-m'));

        if (empty($columns)) {
            return redirect()->back()->with('error', 'يرجى تحديد الأعمدة للتصدير');
        }

        $query = Timesheet::query();

        // إذا المطلوب 'employee_name'، نفذ join لجلب اسم الموظف
        if (in_array('employee_name', $columns)) {
            $query->join('employees', 'timesheets.employee_id', '=', 'employees.id');
        }

        // بناء select ديناميكي
        $selects = [];
        foreach ($columns as $col) {
            if ($col === 'employee_name') {
                $selects[] = 'employees.name as employee_name';
            } else if ($col === 'iban') {
                $selects[] = 'iban';
            } else {
                $selects[] = 'timesheets.' . $col;
            }
        }
        $query->select($selects);

        // فلترة الشهر إلا إذا كان all (يعني كل الشهور)
        if ($month !== 'all') {
            $query->whereRaw('DATE_FORMAT(timesheets.work_date, "%Y-%m") = ?', [$month]);
        }

        $data = $query->get();

        // إنشاء ملف Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // رؤوس الأعمدة (تغيير بعض التسميات لجعلها واضحة)
        foreach ($columns as $i => $col) {
            $colLetter = Coordinate::stringFromColumnIndex($i + 1);
            switch ($col) {
                case 'employee_name':
                    $label = 'Employee Name';
                    break;
                case 'hours_worked':
                    $label = 'Duration (hours)';
                    break;
                case 'month_salary':
                    $label = 'Monthly Salary';
                    break;
                case 'is_paid':
                    $label = 'Payment Status';
                    break;
                case 'work_date':
                    $label = 'Month';
                    break;
                case 'iban':
                    $label = 'RIP';
                    break;
                default:
                    $label = ucfirst(str_replace('_', ' ', $col));
            }
            $sheet->setCellValue($colLetter . '1', $label);
        }

        // البيانات مع تحويل القيم الضرورية
        foreach ($data as $rowIndex => $row) {
            foreach ($columns as $colIndex => $col) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
                $cellValue = null;

                switch ($col) {
                    case 'is_paid':
                        $cellValue = $row->is_paid ? 'Paid' : 'Unpaid';
                        break;
                    case 'work_date':
                        $cellValue = Carbon::parse($row->work_date)->format('Y-m');
                        break;
                    case 'employee_name':
                        $cellValue = $row->employee_name;
                        break;
                    case 'iban':
                        $cellValue = $row->iban;
                        break;
                    default:
                        $cellValue = $row->$col;
                }

                $sheet->setCellValue($colLetter . ($rowIndex + 2), $cellValue);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'timesheet_' . ($month === 'all' ? 'all_months' : $month) . '.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);

        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }
    // /**
    //  * Show the form for creating a new resource.
    //  */
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

    /**
     * Ensure all employees with monthly salaries have timesheet entries for each month.
     */
    public function generateMonthlyTimesheets()
    {
        // Get all employees with monthly payment type
        $monthlyEmployees = Employee::where('payment_type', 'monthly')->get();

        $currentMonth = now()->startOfMonth();
        $createdCount = 0;
        $existingCount = 0;

        foreach ($monthlyEmployees as $employee) {
            // Check if timesheet already exists for this employee for the current month
            $existingTimesheet = Timesheet::where('employee_id', $employee->id)
                ->whereYear('work_date', $currentMonth->year)
                ->whereMonth('work_date', $currentMonth->month)
                ->first();

            if ($existingTimesheet) {
                $existingCount++;
                continue;
            }

            // Create a new timesheet for this employee for the current month
            Timesheet::create([
                'employee_id' => $employee->id,
                'work_date' => $currentMonth->toDateString(),
                'hours_worked' => 0,
                'project_id' => null,
                'month_salary' => $employee->rate,
                'is_paid' => false,
            ]);

            $createdCount++;
        }

        flash()->success("Generated timesheets for {$createdCount} employees. {$existingCount} employees already had timesheets for this month.");
        return redirect()->route('admin.timesheets.index');
    }
}
