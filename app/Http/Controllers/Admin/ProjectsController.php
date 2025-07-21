<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\Client;
use App\Models\Development;
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
        $user = Auth::user();
        $selectedMonth = $request->input('month', now()->format('Y-m'));

        // تاريخ البداية والنهاية إذا تم اختيار شهر
        if ($selectedMonth !== 'all') {
            $startDate = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $selectedMonth)->endOfMonth();
        }

        // جلب المشاريع حسب نوع المستخدم
        $projectsQuery = Project::query();

        // إذا كان موظف، جلب فقط المشاريع المرتبطة به
        if ($user->type === 'employee') {
            $employeeId = $user->employee->id ?? null;

            $projectsQuery->whereHas('employees', function ($query) use ($employeeId) {
                $query->where('employee_id', $employeeId);
            });
        }

        // تطبيق التصفية بالشهر
        if ($selectedMonth !== 'all') {
            $projectsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $projects = $projectsQuery->with('payments')->get();

        // التجميع حسب العملة
        $totalsByCurrency = $projects->groupBy('currency')->map(function ($group) {
            return $group->sum('total_amount');
        });

        // أسعار الصرف إلى الدينار الجزائري
        $exchangeRates = [
            'USD' => 135.00,
            'EUR' => 145.00,
            'DZD' => 1,
        ];

        // حساب المجموع الكلي بالدينار الجزائري
        $totalInDZD = $totalsByCurrency->reduce(function ($carry, $amount, $currency) use ($exchangeRates) {
            $rate = $exchangeRates[$currency] ?? 1;
            return $carry + ($amount * $rate);
        }, 0);

        // عدد المشاريع
        $projectCount = $projects->count();

        // الأشهر المتاحة في المشاريع
        $availableMonths = Project::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as value, DATE_FORMAT(created_at, "%M %Y") as label')
            ->groupBy('value', 'label')
            ->orderBy('value', 'desc')
            ->get()
            ->toArray();

        // حساب الأيام المتبقية والمبلغ المتبقي لكل مشروع
        foreach ($projects as $project) {
            $project->remainingDays = $project->delivery_date
                ? Carbon::now()->diffInDays(Carbon::parse($project->delivery_date), false)
                : null;

            $project->remaining_amount = $project->total_amount - $project->payments->sum('amount');
        }


        $developments = Development::all(); // Assuming you want to fetch all developments

        return view('admin.projects.index', compact(
            'projects',
            'totalsByCurrency',
            'projectCount',
            'totalInDZD',
            'availableMonths',
            'selectedMonth',
            'developments'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // منع الموظفين من إنشاء مشاريع
        if (Auth::user()->type === 'employee') {
            abort(403, 'Unauthorized');
        }

        $project = new Project();

        // تحميل المستخدمين بصلاحيات admin و manager فقط
        $users = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['admin', 'manager']);
        })->get();

        $clients   = Client::all();
        $employees = Employee::all();

        // يمكنك أيضًا تمرير قائمة العملات إن كنت تستخدم exchange_rate أو currency
        $currencies = ['USD', 'EUR', 'DZD', 'SAR']; // أو جلبها من جدول إن كنت تستخدم جدول عملات

        return view('admin.projects.create', compact(
            'project',
            'users',
            'clients',
            'employees',
            'currencies'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectRequest $request)
    {
        if (Auth::user()->type === 'employee') {
            abort(403, 'Unauthorized');
        }

        $data = $request->validated();

        $data['user_id'] = Auth::id();
        $data['client_id'] = $request->client_id;

        $data['is_manual'] = $request->boolean('is_manual');

        if ($data['is_manual']) {
            $data['manual_hours_spent'] = $request->manual_hours_spent ?? 0;
            $data['manual_cost'] = $request->manual_cost ?? 0;
        } else {
            $data['manual_hours_spent'] = null;
            $data['manual_cost'] = null;
        }

        unset($data['attachment'], $data['employee_id']);

        $project = Project::create($data);

        if (!$data['is_manual']) {
            $employeeIds = $request->input('employee_id', []);
            if (!empty($employeeIds)) {
                $project->employees()->sync($employeeIds);
            }
        }

        // تسجيل دفعة كاملة تلقائياً إذا المشروع يدوي
        if ($data['is_manual']) {
            $project->payments()->create([
                'amount' => $data['total_amount'],
                'paid_at' => now(),
                // أضف حقول أخرى حسب جدول المدفوعات عندك
            ]);
        }

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

        if ($request->filled('links')) {
            foreach ($request->input('links') as $link) {
                if (!empty($link['url'])) {
                    $project->links()->create([
                        'url' => $link['url'],
                        'label' => $link['label'] ?? null,
                    ]);
                }
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
        $user = Auth::user();

        // تحقق إذا كان الموظف مرتبطاً بالمشروع
        if ($user->type === 'employee') {
            $employeeId = $user->employee->id ?? null;
            if (!$employeeId || !$project->employees()->where('employees.id', $employeeId)->exists()) {
                abort(403, 'Unauthorized: You can only view projects assigned to you.');
            }
        }

        // المهام المكتملة
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

        // جلب بيانات الموظفين (بما فيها المعدل والعملة)
        $employees = Employee::whereIn('id', array_keys($hoursByEmployee))->get()->keyBy('id');

        // حساب التكلفة لكل موظف حسب المعدل والعملة الخاصة به
        $costsByEmployee = [];
        $totalHours = 0;
        $totalCostDZD = 0;
        foreach ($hoursByEmployee as $employeeId => $hours) {
            $employee = $employees[$employeeId];
            $totalHours += $hours;
            $rate = $employee->rate; // الأجر حسب موظف (افتراض)
            $cost = $hours * $rate;
            $costsByEmployee[$employeeId] = $cost;

            // تحويل التكلفة إلى دينار جزائري (مثلاً إذا الموظف بعملة أخرى تحتاج تحويل)
            // هنا نفترض المعدل بالدينار، أو اضف التحويل حسب العملة:
            $currency = $employee->currency ?? $project->currency;
            if ($currency !== 'DZD') {
                // مثال لتحويل (يجب تعديل حسب قاعدة بيانات أسعار الصرف)
                $exchangeRates = [
                    'USD' => 140,  // مثلا 1 دولار = 140 دينار
                    'EUR' => 150,  // 1 يورو = 150 دينار
                    // أضف العملات حسب الحاجة
                ];
                $rateToDZD = $exchangeRates[$currency] ?? 1;
                $totalCostDZD += $cost * $rateToDZD;
            } else {
                $totalCostDZD += $cost;
            }
        }

        // حساب المبالغ المالية للمشروع
        $paidAmount = $project->payments->sum('amount');
        $remainingAmount = $project->total_amount - $paidAmount;

        // جلب التطويرات (التحديثات) المرتبطة بالمشروع
        $developments = $project->developments()->latest()->get();

        return view('admin.projects.show', compact(
            'project',
            'totalHours',
            'totalCostDZD',
            'paidAmount',
            'remainingAmount',
            'hoursByEmployee',
            'employees',
            'costsByEmployee',
            'developments'  // أضفنا التطويرات هنا
        ));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $user = Auth::user();

        // إذا كان الدور "موظف" Employee
        if ($user->type === 'employee') {
            // نتحقق هل الموظف مرتبط بهذا المشروع
            $isAssigned = $project->employees()->where('id', $user->employee->id)->exists();

            if (! $isAssigned) {
                abort(403, 'Unauthorized: You can only edit projects assigned to you.');
            }

            // يمكن جلب فقط الموظف الحالي لأنه موظف
            $employees = Employee::where('id', $user->employee->id)->get();
        } else {
            // باقي المستخدمين (مدير، أدمن، ... ) يشوفوا كل الموظفين
            $employees = Employee::all();
        }

        // المستخدمين والعملاء ممكن يشوفوا الكل، أو حسب صلاحياتك تعدل هنا لو تريد
        $users = User::all();
        $clients = Client::all();

        // تمرير معلومات المشروع، المستخدمين، العملاء، الموظفين، ومؤشر إذا كان المشروع يدوي
        return view('admin.projects.edit', compact('project', 'users', 'clients', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectRequest $request, Project $project)
    {
        $user = Auth::user();

        // تحقق صلاحية الموظف
        if ($user->type === 'employee') {
            $isAssigned = $project->employees()->where('id', $user->employee->id)->exists();
            if (! $isAssigned) {
                abort(403, 'Unauthorized: You can only update projects assigned to you.');
            }
        }

        $data = $request->validated();
        $data['user_id'] = $user->id; // معرف المستخدم الحالي
        $data['client_id'] = $request->client_id;

        unset($data['attachment']);
        unset($data['employee_id']);

        $project->update($data);
        // إذا كان المشروع يدوي، حدث عدد الساعات والتكلفة اليدوية
        if ($project->is_manual) {
            $project->update([
                'manual_hours_spent' => $request->input('manual_hours_spent'),
                'manual_cost' => $request->input('manual_cost'),
            ]);
        }


        $employeeIds = $request->input('employee_id', []);

        // إذا كان المستخدم موظف، يسمح فقط بتحديث نفسه في الموظفين المرتبطين
        if ($user->type === 'employee') {
            $employeeIds = [$user->employee->id];
        }

        if (!empty($employeeIds)) {
            $project->employees()->sync($employeeIds);
        } else {
            $project->employees()->sync([]);
        }

        // حذف المرفقات المحددة
        if ($request->has('delete_attachments')) {
            foreach ($request->delete_attachments as $attachmentId) {
                $attachment = $project->attachments()->find($attachmentId);
                if ($attachment && Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
                $attachment?->delete();
            }
        }

        // رفع ملفات جديدة
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

        // التعامل مع روابط المشروع
        $existingIds = [];

        if ($request->has('links.existing')) {
            foreach ($request->input('links.existing') as $id => $linkData) {
                $link = $project->links()->find($id);
                if ($link && !empty($linkData['url'])) {
                    $link->update([
                        'url' => $linkData['url'],
                        'label' => $linkData['label'] ?? null,
                    ]);
                    $existingIds[] = $link->id;
                }
            }
        }

        $project->links()->whereNotIn('id', $existingIds)->delete();

        if ($request->has('links.new')) {
            foreach ($request->input('links.new') as $linkData) {
                if (!empty($linkData['url'])) {
                    $project->links()->create([
                        'url' => $linkData['url'],
                        'label' => $linkData['label'] ?? null,
                    ]);
                }
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
        $user = Auth::user();

        // إذا كان المستخدم موظف، يتحقق هل المشروع مرتبط به
        if ($user->type === 'employee') {
            $isAssigned = $project->employees()->where('id', $user->employee->id)->exists();
            if (! $isAssigned) {
                abort(403, 'Unauthorized: You can only delete projects assigned to you.');
            }
        }

        $project->delete();

        flash()->success('Project deleted successfully');
        return redirect()->route('admin.projects.index');
    }
}
