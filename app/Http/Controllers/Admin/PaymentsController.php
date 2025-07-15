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
        $invoice = Invoice::with(['wallet', 'project'])->findOrFail($request->invoice_id);

        $data = $request->validated();
        $data['invoice_id'] = $invoice->id;
        $data['amount'] = $invoice->amount;

        // العملة الخاصة بالفاتورة (من المشروع) وعملة المحفظة
        $invoiceCurrency = $invoice->project->currency ?? null;
        $walletCurrency = $invoice->wallet->currency ?? null;

        // إذا نفس العملة: سعر الصرف = 1
        if ($invoiceCurrency === $walletCurrency) {
            $data['exchange_rate'] = 1;
            $convertedAmount = $invoice->amount;
        } else {
            $data['exchange_rate'] = $request->exchange_rate;
            $convertedAmount = $invoice->amount * $request->exchange_rate;
        }

        $payment = Payment::create($data);

        // استخدام المبلغ المحول فقط
        if ($invoice->wallet) {
            $invoice->wallet->increment('balance', $convertedAmount);
        }

        // تحديث حالة الفاتورة إلى مدفوعة إذا كانت غير مدفوعة
        if (!$invoice->is_paid) {
            $invoice->is_paid = 1;
            $invoice->save();
        }

        flash()->success('Payment created successfully and wallet balance updated');
        return redirect()->route('admin.payments.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // جلب الدفعة مع الفاتورة المرتبطة (مع علاقات المحفظة والمشروع)
        $payment = Payment::with('invoice.wallet', 'invoice.project')->findOrFail($id);

        // جلب كل الفواتير لعرضها في السلكت (اختياري إذا تريد عرض السلكت حسب الكود اللي أعطيتك)
        $invoices = Invoice::with('wallet', 'project')->get();

        // عرض صفحة العرض وتمرير البيانات
        return view('admin.payments.show', compact('payment', 'invoices'));
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
        // الفاتورة القديمة مع المحفظة والمشروع
        $oldInvoice = $payment->invoice()->with(['wallet', 'project'])->first();
        $oldConvertedAmount = $payment->amount * ($payment->exchange_rate ?? 1);

        // الفاتورة الجديدة مع المحفظة والمشروع
        $newInvoice = Invoice::with(['wallet', 'project'])->findOrFail($request->invoice_id);

        $data = $request->validated();
        $data['invoice_id'] = $newInvoice->id;
        $data['amount'] = $newInvoice->amount;

        // عملات الفاتورة والمحفظة الجديدة
        $invoiceCurrency = $newInvoice->project->currency ?? null;
        $walletCurrency = $newInvoice->wallet->currency ?? null;

        // تحديد سعر الصرف والمبلغ المحول
        if ($invoiceCurrency === $walletCurrency) {
            $data['exchange_rate'] = 1;
            $newConvertedAmount = $newInvoice->amount;
        } else {
            $data['exchange_rate'] = $request->exchange_rate;
            $newConvertedAmount = $newInvoice->amount * $request->exchange_rate;
        }

        // تحديث الدفعة
        $payment->update($data);

        // خصم المبلغ المحول القديم من المحفظة القديمة
        if ($oldInvoice && $oldInvoice->wallet) {
            $oldInvoice->wallet->decrement('balance', $oldConvertedAmount);

            // تحقق إذا هذه الفاتورة لم يعد لها دفعات مرتبطة، إذا نعم اجعلها غير مدفوعة
            $remainingPayments = $oldInvoice->payments()->sum('amount');
            if ($remainingPayments == 0) {
                $oldInvoice->is_paid = 0;
                $oldInvoice->save();
            }
        }

        // إضافة المبلغ المحول الجديد إلى المحفظة الجديدة
        if ($newInvoice->wallet) {
            $newInvoice->wallet->increment('balance', $newConvertedAmount);
        }

        // تحديث حالة الفاتورة الجديدة إلى مدفوعة إذا لم تكن كذلك
        if (!$newInvoice->is_paid) {
            $newInvoice->is_paid = 1;
            $newInvoice->save();
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
