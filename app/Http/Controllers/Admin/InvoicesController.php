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

    public function show(Invoice $invoice)
    {
        $projects = Project::all(); // لو تحتاج للخيارات أو عرض بيانات المشروع
        $wallets = Wallet::all();

        // احسب بيانات المشروع المالية
        $paidAmount = $invoice->project->invoices()
            ->where('is_paid', true)
            ->sum('amount');
        $totalAmount = $invoice->project->total_amount;
        $remainingAmount = $totalAmount - $paidAmount;
        $paidPercentage = $totalAmount > 0 ? round(($paidAmount / $totalAmount) * 100, 2) : 0;

        return view('admin.invoices.show', compact('invoice', 'wallets', 'paidAmount', 'totalAmount', 'remainingAmount', 'paidPercentage'));
    }

    public function store(InvoiceRequest $request)
    {
        $data = $request->validated();

        // تعيين is_paid تلقائياً كـ false (غير مدفوعة)
        $data['is_paid'] = false;

        // تعيين wallet_id و project_id كما هو من الريكوست
        $data['wallet_id'] = $request->wallet_id;
        $data['project_id'] = $request->project_id;

        $project = Project::with('client')->findOrFail($request->project_id);

        if (!$project->client) {
            return back()->withErrors(['project_id' => 'This project does not have an associated client.']);
        }

        $data['client_id'] = $project->client->id;

        // حساب المبلغ المدفوع مسبقاً للمشروع (الفواتير المدفوعة فقط)
        $paidAmount = $project->invoices()
            ->where('is_paid', true)
            ->sum('amount');

        // حساب المبلغ المتبقي
        $remaining = $project->total_amount - $paidAmount;

        // منع إدخال مبلغ أكبر من المتبقي
        if ($data['amount'] > $remaining) {
            return back()->withErrors(['amount' => "The entered amount ({$data['amount']}) exceeds the remaining amount of the project ({$remaining})."]);
        }

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
        $data['project_id'] = $request->project_id;

        // تعيين is_paid تلقائياً (مثلاً دايماً false أو تحافظ على القيمة الحالية)
        // إذا تريد تبقي القيمة كما هي دون تعديل:
        $data['is_paid'] = $invoice->is_paid;

        $project = Project::with('client')->findOrFail($request->project_id);

        if (!$project->client) {
            return back()->withErrors(['project_id' => 'This project does not have an associated client.']);
        }

        $data['client_id'] = $project->client->id;

        // حساب المبلغ المدفوع مع استثناء الفاتورة الحالية
        $paidAmount = $project->invoices()
            ->where('is_paid', true)
            ->where('id', '!=', $invoice->id)
            ->sum('amount');

        $remaining = $project->total_amount - $paidAmount;

        if ($data['amount'] > $remaining) {
            return back()->withErrors(['amount' => "The entered amount ({$data['amount']}) exceeds the remaining amount of the project ({$remaining})."]);
        }

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

    public function getProjectFinancials(Project $project)
    {
        // فقط الفواتير المدفوعة is_paid = true
        $paidAmount = Invoice::where('project_id', $project->id)
            ->where('is_paid', true)
            ->sum('amount');

        $remaining = $project->total_amount - $paidAmount;

        return response()->json([
            'total' => number_format($project->total_amount, 2, '.', ''),
            'paid' => number_format($paidAmount, 2, '.', ''),
            'remaining' => number_format($remaining, 2, '.', ''),
            'currency' => $project->currency // ✅ أضفنا العملة هنا
        ]);
    }

    public function info($id)
    {
        $invoice = Invoice::with(['project.client', 'wallet'])->findOrFail($id);

        return response()->json([
            'client_name'   => $invoice->project?->client?->name ?? 'N/A',
            'project_name'  => $invoice->project?->name ?? 'N/A',
            'wallet_name'   => $invoice->wallet?->name ?? 'N/A',
            'amount'        => $invoice->amount,
            'currency'      => $invoice->project?->currency ?? 'N/A',
        ]);
    }
}
