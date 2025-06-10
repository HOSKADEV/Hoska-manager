<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoicesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = Invoice::all();

        return view('admin.invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $invoice = new Invoice();
        $projects = Project::all();
        $clients = Client::all();

        return view('admin.invoices.create', compact('invoice', 'projects', 'clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InvoiceRequest $request)
    {
        $data = $request->validated();
        $data['is_paid'] = $request->is_paid;
        $data['project_id'] = $request->project_id;

        // ✅ استخراج العميل المرتبط بالمشروع
        $project = Project::with('client')->findOrFail($request->project_id);

        if (!$project->client) {
            return back()->withErrors(['project_id' => 'هذا المشروع لا يحتوي على عميل مرتبط.']);
        }

        $data['client_id'] = $project->client->id;

        $data['amount'] = $project->total_amount;

        Invoice::create($data);

        flash()->success('Invoice created successfully');
        return redirect()->route('admin.invoices.index');
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
    public function edit(Invoice $invoice)
    {
        $projects = Project::all();
        $clients = Client::all();
        return view('admin.invoices.edit', compact('invoice', 'projects', 'clients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InvoiceRequest $request, Invoice $invoice)
    {
        $data = $request->validated();
        $data['is_paid'] = $request->is_paid;
        $data['project_id'] = $request->project_id;

        // ✅ استخراج العميل والمبلغ من المشروع المرتبط
        $project = Project::with('client')->findOrFail($request->project_id);

        if (!$project->client) {
            return back()->withErrors(['project_id' => 'هذا المشروع لا يحتوي على عميل مرتبط.']);
        }

        $data['client_id'] = $project->client->id;
        $data['amount'] = $project->total_amount;

        $invoice->update($data);

        flash()->success('Invoice updated successfully');
        return redirect()->route('admin.invoices.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        flash()->success('Invoice deleted successfully');
        return redirect()->route('admin.invoices.index');
    }
}
