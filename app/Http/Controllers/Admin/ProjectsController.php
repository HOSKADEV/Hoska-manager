<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProjectsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));

        // تحويل القيمة إلى تاريخ بداية ونهاية الشهر
        if ($selectedMonth !== 'all') {
            $startDate = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $selectedMonth)->endOfMonth();
            $projects = Project::whereBetween('created_at', [$startDate, $endDate])->get();
        } else {
            $projects = Project::all();
        }

        // التجميع حسب العملة
        $totalsByCurrency = $projects->groupBy('currency')->map(function ($group) {
            return $group->sum('total_amount');
        });

        // عدد المشاريع
        $projectCount = $projects->count();

        // أسعار الصرف إلى الدينار الجزائري
        $exchangeRates = [
            'USD' => 135.00,
            'EUR' => 145.00,
            'DZD' => 1,
        ];

        // حساب المجموع الكلي محول إلى الدينار الجزائري
        $totalInDZD = 0;
        foreach ($totalsByCurrency as $currency => $amount) {
            $rate = $exchangeRates[$currency] ?? 1;
            $totalInDZD += $amount * $rate;
        }

        // الأشهر المتوفرة في المشاريع
        $availableMonths = Project::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as value, DATE_FORMAT(created_at, "%M %Y") as label')
            ->groupBy('value', 'label')
            ->orderBy('value', 'desc')
            ->get()
            ->toArray();

        // حساب الأيام المتبقية والمبلغ المتبقي
        foreach ($projects as $project) {
            $project->remainingDays = $project->delivery_date
                ? Carbon::now()->diffInDays(Carbon::parse($project->delivery_date), false)
                : null;

            $project->remaining_amount = $project->total_amount - $project->payments->sum('amount');
        }

        return view('admin.projects.index', compact(
            'projects',
            'totalsByCurrency',
            'projectCount',
            'totalInDZD',
            'availableMonths',
            'selectedMonth'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $project = new Project();
        $users = User::all();
        $clients = Client::all();
        $employees = Employee::all();

        return view('admin.projects.create', compact('project', 'users', 'clients', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectRequest $request)
    {

        // التحقق من صحة الطلب
        $data = $request->validated();
        $data['user_id'] = Auth::user()->id; // استخدام معرف المستخدم الحالي بدلاً من معرف المستخدم من الطلب
        $data['client_id'] = $request->client_id;

        // إزالة الحقول غير المرتبطة مباشرة بـ Project
        unset($data['attachment'], $data['employee_id']);

        // إنشاء المشروع
        $project = Project::create($data);

        // استخراج employee_ids من الطلب
        $employeeIds = $request->input('employee_id', []);

        // ربط الموظفين بالمشروع
        if (!empty($employeeIds)) {
            $project->employees()->sync($employeeIds);
        }

        // رفع الملفات وربطها بالمشروع
        if ($request->hasFile('attachment')) {
            foreach ($request->file('attachment') as $file) {
                $filename = rand() . time() . $file->getClientOriginalName();
                $path = $file->store('attachments', 'public');

                $project->attachments()->create([
                    'file_name' => $filename,
                    'file_path' => $path,
                ]);
            }
        }

        flash()->success('Project created successfully');
        return redirect()->route('admin.projects.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        // استدعاء المهام المكتملة فقط
        $tasks = $project->tasks()->where('status', 'completed')->get();

        // حساب مجموع الساعات لكل موظف
        $hoursByEmployee = [];

        foreach ($tasks as $task) {
            $hours = $task->duration_in_hours;

            if (!isset($hoursByEmployee[$task->employee_id])) {
                $hoursByEmployee[$task->employee_id] = 0;
            }
            $hoursByEmployee[$task->employee_id] += $hours;
        }

        // جلب بيانات الموظفين (معدل الساعة)
        $employees = Employee::whereIn('id', array_keys($hoursByEmployee))->get()->keyBy('id');

        $totalHours = 0;
        $totalCostDZD = 0;

        foreach ($hoursByEmployee as $employeeId => $hours) {
            $employee = $employees[$employeeId];

            $totalHours += $hours;

            // تحويل الأجر إلى DZD (إذا الأجر بعملة مختلفة يمكنك تعديلها حسب العملة)
            $rateDZD = $employee->rate; // افترض أن الأجر بالدينار الجزائري مباشرة، أو قم بتحويل العملة

            $totalCostDZD += $hours * $rateDZD;
        }

        return view('admin.projects.show', compact('project', 'totalHours', 'totalCostDZD'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $users = User::all();
        $clients = Client::all();
        $employees = Employee::all();
        return view('admin.projects.edit', compact('project', 'users', 'clients', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectRequest $request, Project $project)
    {

        $data = $request->validated();
        $data['user_id'] = Auth::user()->id; // استخدام معرف المستخدم الحالي بدلاً من معرف المستخدم من الطلب
        $data['client_id'] = $request->client_id;

        unset($data['attachment']); // نحذف المرفقات من البيانات الرئيسية
        unset($data['employee_id']); // لأننا سنستخدم employee_ids للمزامنة

        // تحديث بيانات المشروع
        $project->update($data);

        // استخرج مصفوفة الموظفين من الطلب
        $employeeIds = $request->input('employee_id', []);

        // مزامنة الموظفين (إضافة وحذف تلقائي حسب المصفوفة)
        if (!empty($employeeIds)) {
            $project->employees()->sync($employeeIds);
        } else {
            // لو المصفوفة فارغة، تفريغ العلاقة:
            $project->employees()->sync([]);
        }

        // حذف المرفقات المختارة من المستخدم
        if ($request->has('delete_attachments')) {
            foreach ($request->delete_attachments as $attachmentId) {
                $attachment = $project->attachments()->find($attachmentId);
                if ($attachment && Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
                $attachment?->delete();
            }
        }

        // رفع ملفات جديدة (إن وجدت)
        if ($request->hasFile('attachment')) {
            foreach ($request->file('attachment') as $file) {
                $filename = rand() . time() . $file->getClientOriginalName();
                $path = $file->storeAs('attachments', $filename, 'public');

                $project->attachments()->create([
                    'file_name' => $filename,
                    'file_path' => $path,
                ]);
            }
        }

        flash()->success('Project updated successfully');
        return redirect()->route('admin.projects.index');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        flash()->success('Project deleted successfully');
        return redirect()->route('admin.projects.index');
    }
}
