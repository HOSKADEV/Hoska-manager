<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;

class AdminController extends Controller
{
    // public function index()
    // {
    //     return view('admin.index');
    // }

    public function index()
    {
        $totalTasks = Task::count();
        $completedTasks = Task::where('status', 'completed')->count();

        $totalProjects = Project::count();
        $totalClients = Client::count();

        $monthlyEarnings = 0;

        $user = Auth::user();

        if ($user->type === 'employee') {
            $employee = Employee::where('user_id', $user->id)->first();

            if ($employee) {
                $salaryType = $employee->payment_type;  // 'monthly', 'hourly', 'per_project'
                $rate = $employee->rate ?? 0;

                if ($salaryType === 'monthly') {
                    // راتب شهري ثابت
                    $monthlyEarnings = $rate;
                } elseif ($salaryType === 'hourly') {
                    // حساب مجموع عدد الساعات من start_time إلى end_time للمهام المنجزة خلال هذا الشهر
                    $completedTasks = Task::where('employee_id', $employee->id)
                        ->where('status', 'completed')
                        ->whereYear('end_time', now()->year)
                        ->whereMonth('end_time', now()->month)
                        ->get();

                    $totalHours = $completedTasks->sum(function ($task) {
                        return \Carbon\Carbon::parse($task->start_time)->diffInHours(\Carbon\Carbon::parse($task->end_time));
                    });

                    $monthlyEarnings = $totalHours * $rate;
                } elseif ($salaryType === 'per_project') {
                    // عدد المشاريع التي لديه فيها مهام مكتملة خلال الشهر
                    $completedProjects = Project::whereHas('tasks', function ($query) use ($employee) {
                        $query->where('employee_id', $employee->id)
                            ->where('status', 'completed')
                            ->whereYear('end_time', now()->year)
                            ->whereMonth('end_time', now()->month);
                    })->count();

                    $monthlyEarnings = $completedProjects * $rate;
                }
            }
        } elseif ($user->type === 'admin') {
            // مجموع المدفوعات المحصلة في هذا الشهر
            $payments = Payment::with('invoice.project')
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->get();

            // تحويل حسب عملة المشروع
            $exchangeRatesToUSD = [
                'USD' => 1,
                'EUR' => 1.10,
                'DZD' => 0.0074,
            ];

            foreach ($payments as $payment) {
                $currency = strtoupper($payment->invoice->project->currency ?? 'USD');
                $rate = $exchangeRatesToUSD[$currency] ?? 1;
                $monthlyEarnings += $payment->amount * $rate;
            }
        }

        $completionPercentage = $totalTasks > 0
            ? round(($completedTasks / $totalTasks) * 100)
            : 0;

        return view('admin.index', compact(
            'completionPercentage',
            'totalTasks',
            'completedTasks',
            'totalProjects',
            'totalClients',
            'monthlyEarnings'
        ));
    }

    public function profile()
    {
        $user = Auth::user();
        $employee = $user->employee; // حسب العلاقة عندك
        return view('admin.profile', compact('user', 'employee'));
    }

    public function profile_save(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'required',
            'phone' => 'nullable',
            'password' => 'nullable|min:6|confirmed',
            'avatar' => 'nullable|image|mimes:png,jpg,svg,jpeg',
        ]);

        // بيانات المستخدم التي نحدثها (بدون phone لأنه في جدول contacts)
        if ($user->type === 'admin') {
            $data = $request->except(['_token', '_method', 'avatar', 'password', 'password_confirmation', 'phone']);
        } else {
            $data = $request->only(['name']);
        }

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('uploads', 'custom');
        }

        // تحديث بيانات المستخدم
        $user->update($data);

        // تحديث رقم الهاتف في جدول contacts المرتبط بالموظف
        if ($request->filled('phone')) {
            $employee = $user->employee; // تأكد من العلاقة في موديل User

            if ($employee) {
                $contact = $employee->contacts()->first();
                if ($contact) {
                    $contact->update(['phone' => $request->phone]);
                } else {
                    // إذا ما فيه contact سابق، ننشئ واحد جديد
                    $employee->contacts()->create(['phone' => $request->phone]);
                }
            }
        }

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
}
