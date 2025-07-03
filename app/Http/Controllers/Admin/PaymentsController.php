<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payments = Payment::all();

        return view('admin.payments.index', compact('payments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $payment = new Payment();
        $invoices = Invoice::all();
        // $invoices = Invoice::pluck('id', 'invoice_number')->toArray();

        return view('admin.payments.create', compact('payment', 'invoices'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentRequest $request)
    {
        $data = $request->validated();
        $invoice = Invoice::with('wallet')->findOrFail($request->invoice_id);

        $data['invoice_id'] = $request->invoice_id;
        $data['amount'] = $invoice->amount;

        $payment = Payment::create($data);

        // تحديث رصيد المحفظة المرتبطة بالفاتورة
        if ($invoice->wallet) {
            $invoice->wallet->increment('balance', $invoice->amount);
        }

        flash()->success('Payment created successfully and wallet balance updated');
        return redirect()->route('admin.payments.index');
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
    public function edit(Payment $payment)
    {
        $invoices = Invoice::all();
        return view('admin.payments.edit', compact('payment', 'invoices'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentRequest $request, Payment $payment)
    {
        $oldInvoice = $payment->invoice()->with('wallet')->first(); // الفاتورة القديمة
        $oldAmount = $payment->amount;

        $data = $request->validated();
        $newInvoice = Invoice::with('wallet')->findOrFail($request->invoice_id);

        $data['invoice_id'] = $request->invoice_id;
        $data['amount'] = $newInvoice->amount;

        $payment->update($data);

        // تعديل رصيد المحفظة القديمة - نخصم المبلغ القديم
        if ($oldInvoice && $oldInvoice->wallet) {
            $oldInvoice->wallet->decrement('balance', $oldAmount);
        }

        // إضافة المبلغ الجديد للمحفظة الجديدة
        if ($newInvoice->wallet) {
            $newInvoice->wallet->increment('balance', $newInvoice->amount);
        }

        flash()->success('Payment updated successfully and wallet balances adjusted');
        return redirect()->route('admin.payments.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();

        flash()->success('Payment deleted successfully');
        return redirect()->route('admin.payments.index');
    }
}
