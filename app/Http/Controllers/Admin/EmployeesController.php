<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Models\User;
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
                'is_marketer' => $request->has('user.is_marketer'), // إضافة حالة التسويق
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
                    'is_marketer' => $request->has('user.is_marketer'),
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
                        'is_marketer' => $request->has('user.is_marketer'),
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
}
