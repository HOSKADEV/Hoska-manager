<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProjectsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::all();

        return view('admin.projects.index', compact('projects'));
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
    public function show(string $id)
    {
        //
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
