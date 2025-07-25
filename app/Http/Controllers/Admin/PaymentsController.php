<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payments = Payment::latest()->get();

        return view('admin.payments.index', compact('payments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $payment = new Payment();

        // الفواتير غير المدفوعة
        $invoices = Invoice::where('is_paid', false)->with(['wallet', 'project'])->get();

        // جلب كل المحافظ مع العملة واسم المحفظة (مهم للـ JS)
        $walletsFull = Wallet::all(); // لا تستخدم pluck هنا، بل كامل الكائن

        // لجعل الـ select يعمل بترتيب الاسم فقط، يمكنك تحضير قائمة منفصلة:
        $wallets = $walletsFull->pluck('name', 'id');

        return view('admin.payments.create', compact('payment', 'invoices', 'wallets', 'walletsFull'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentRequest $request)
    {
        $invoice = Invoice::with(['wallet', 'project'])->findOrFail($request->invoice_id);

        $data = $request->validated();
        $data['invoice_id'] = $invoice->id;
        $data['wallet_id'] = $request->wallet_id;
        $data['amount'] = $invoice->amount;

        $invoiceCurrency = $invoice->project->currency ?? null;
        $wallet = Wallet::findOrFail($data['wallet_id']);
        $walletCurrency = $wallet->currency ?? null;

        if ($invoiceCurrency === $walletCurrency) {
            $data['exchange_rate'] = 1;
            $convertedAmount = $invoice->amount;
        } else {
            $data['exchange_rate'] = $request->exchange_rate;
            $convertedAmount = $invoice->amount * $request->exchange_rate;
        }

        $payment = Payment::create($data);

        // خصم من المحفظة المختارة
        $wallet->increment('balance', $convertedAmount);

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
        $invoices = Invoice::where('is_paid', false)
            ->orWhere('id', $payment->invoice_id)
            ->get();

        // جلب جميع المحافظ مع الحقول id, name, currency
        $wallets = Wallet::select('id', 'name', 'currency')->get();

        return view('admin.payments.edit', compact('payment', 'invoices', 'wallets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentRequest $request, Payment $payment)
    {
        $oldConvertedAmount = $payment->amount * ($payment->exchange_rate ?? 1);

        $oldWallet = Wallet::find($payment->wallet_id);
        if ($oldWallet) {
            $oldWallet->increment('balance', $oldConvertedAmount); // استرجاع الرصيد
        }

        $newInvoice = Invoice::with(['wallet', 'project'])->findOrFail($request->invoice_id);
        $data = $request->validated();
        $data['invoice_id'] = $newInvoice->id;
        $data['wallet_id'] = $request->wallet_id;
        $data['amount'] = $newInvoice->amount;

        $invoiceCurrency = $newInvoice->project->currency ?? null;
        $newWallet = Wallet::findOrFail($data['wallet_id']);
        $walletCurrency = $newWallet->currency ?? null;

        if ($invoiceCurrency === $walletCurrency) {
            $data['exchange_rate'] = 1;
            $newConvertedAmount = $newInvoice->amount;
        } else {
            $data['exchange_rate'] = $request->exchange_rate;
            $newConvertedAmount = $newInvoice->amount * $request->exchange_rate;
        }

        $payment->update($data);

        $newWallet->decrement('balance', $newConvertedAmount);

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
