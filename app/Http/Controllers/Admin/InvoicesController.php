<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Wallet;
use Illuminate\Http\Request;

class InvoicesController extends Controller
{
    public function index()
    {
        $invoices = Invoice::all();
        return view('admin.invoices.index', compact('invoices'));
    }

    public function create()
    {
        $invoice = new Invoice();
        $projects = Project::all();
        $clients = Client::all();
        $wallets = Wallet::all(); // جلب المحافظ

        return view('admin.invoices.create', compact('invoice', 'projects', 'clients', 'wallets'));
    }

    public function store(InvoiceRequest $request)
    {
        $data = $request->validated();
        $data['wallet_id'] = $request->wallet_id; // حفظ المحفظة المختارة
        $data['is_paid'] = $request->is_paid;
        $data['project_id'] = $request->project_id;

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

    public function edit(Invoice $invoice)
    {
        $projects = Project::all();
        $clients = Client::all();
        $wallets = Wallet::all(); // جلب المحافظ

        return view('admin.invoices.edit', compact('invoice', 'projects', 'clients', 'wallets'));
    }
    
    public function update(InvoiceRequest $request, Invoice $invoice)
    {
        $data = $request->validated();
        $data['wallet_id'] = $request->wallet_id;
        $data['is_paid'] = $request->is_paid;
        $data['project_id'] = $request->project_id;

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

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        flash()->success('Invoice deleted successfully');
        return redirect()->route('admin.invoices.index');
    }
}
