<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $typeFilter = $request->input('type', 'all');

        $contractsQuery = Contract::with(['contractable', 'user']);

        // Apply type filter
        if ($typeFilter !== 'all') {
            $contractsQuery->where('type', $typeFilter);
        }

        $contracts = $contractsQuery->latest()->get();

        return view('admin.contracts.index', compact('contracts', 'typeFilter'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::all();
        $projects = Project::all();

        return view('admin.contracts.create', compact('employees', 'projects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:employee,project',
            // 'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'contractable_id' => 'required|integer',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();

        // Set the contractable type based on the selected type
        if ($request->type === 'employee') {
            $data['contractable_type'] = Employee::class;
        } else {
            $data['contractable_type'] = Project::class;
        }

        Contract::create($data);

        flash()->success('Contract created successfully');
        return redirect()->route('admin.contracts.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contract $contract)
    {
        return view('admin.contracts.show', compact('contract'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contract $contract)
    {
        $employees = Employee::all();
        $projects = Project::all();

        return view('admin.contracts.edit', compact('contract', 'employees', 'projects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contract $contract)
    {
        $request->validate([
            'type' => 'required|in:employee,project',
            // 'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'contractable_id' => 'required|integer',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();

        // Set the contractable type based on the selected type
        if ($request->type === 'employee') {
            $data['contractable_type'] = Employee::class;
        } else {
            $data['contractable_type'] = Project::class;
        }

        $contract->update($data);

        flash()->success('Contract updated successfully');
        return redirect()->route('admin.contracts.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contract $contract)
    {
        $contract->delete();

        flash()->success('Contract deleted successfully');
        return redirect()->route('admin.contracts.index');
    }
}
