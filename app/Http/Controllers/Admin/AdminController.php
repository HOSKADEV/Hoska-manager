<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $monthlyEarnings = Payment::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount'); // تأكد اسم العمود في جدول الدفع

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
