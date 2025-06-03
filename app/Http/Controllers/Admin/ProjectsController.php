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
        $data = $request->validated();
        $request->validate([
            'attachment' => 'required|file|max:10240', // 10 MB مثلاً
        ]);
        $data['user_id'] = $request->user_id;
        $data['client_id'] = $request->client_id;
        $data['employee_id'] = $request->employee_id;

        unset($data['attachment']);

        // إنشاء المشروع
        $project = Project::create($data);

        // تخزين الملف
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = rand() . time() . $file->getClientOriginalName();
            $path = $file->store('attachments', 'public');
            // إنشاء سجل attachment
            $project->attachments()->create([
                'file_name' => $filename,
                'file_path' => $path,
            ]);
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
        $data['user_id'] = $request->user_id;
        $data['client_id'] = $request->client_id;
        $data['employee_id'] = $request->employee_id;
        unset($data['attachment']);
        $project->update($data);



        if ($request->hasFile('attachment')) {
            // جلب أول مرفق مرتبط بالمشروع
            $oldAttachment = $project->attachments()->first();

            if ($oldAttachment) {
                // التحقق من وجود الملف فعليًا قبل حذفه
                if (Storage::disk('public')->exists($oldAttachment->file_path)) {
                    Storage::disk('public')->delete($oldAttachment->file_path);
                }

                // حذف السجل من قاعدة البيانات
                $oldAttachment->delete();
            }

            // رفع الملف الجديد
            $file = $request->file('attachment');
            $filename = rand() . time() . $file->getClientOriginalName();
            $path = $file->storeAs('attachments', $filename, 'public');

            $project->attachments()->create([
                'file_name' => $filename,
                'file_path' => $path,
            ]);
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
