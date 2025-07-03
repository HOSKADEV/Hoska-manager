<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class WalletTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = WalletTransaction::with(['wallet', 'relatedWallet'])->latest();

        // فلترة حسب محفظة معينة
        if ($request->filled('wallet_id')) {
            $query->where('wallet_id', $request->wallet_id);
        }

        // فلترة حسب نوع العملية
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $transactions = $query->paginate(20);

        $wallets = Wallet::all();

        return view('admin.wallet_transactions.index', compact('transactions', 'wallets'));
    }

    /**
     * عرض نموذج إضافة معاملة جديدة - هنا يمكن الاختيار النوع (expense, income, transfer, ...).
     */
    public function create()
    {
        $wallets = Wallet::all(); // تأكد من تمرير المحافظ للاختيار
        return view('admin.wallet_transactions.create', compact('wallets'));
    }

    /**
     * تخزين المعاملة الجديدة (مصروف - دخل - تحويل - تمويل - سحب)
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:expense,income,transfer,withdraw,funding',
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'related_wallet_id' => 'required_if:type,transfer|nullable|exists:wallets,id',
            'exchange_rate' => 'required_if:type,transfer|nullable|numeric|min:0.0001',
        ]);

        $wallet = Wallet::findOrFail($request->wallet_id);

        DB::beginTransaction();

        try {
            switch ($request->type) {
                case 'expense':
                case 'withdraw':
                    if ($wallet->balance < $request->amount) {
                        return redirect()->back()->withErrors(['amount' => 'Insufficient wallet balance.'])->withInput();
                    }
                    $wallet->decrement('balance', $request->amount);
                    break;

                case 'income':
                case 'funding':
                    $wallet->increment('balance', $request->amount);
                    break;

                case 'transfer':
                    if ($request->wallet_id == $request->related_wallet_id) {
                        return redirect()->back()->withErrors(['related_wallet_id' => 'Cannot transfer to the same wallet.'])->withInput();
                    }
                    $relatedWallet = Wallet::findOrFail($request->related_wallet_id);

                    $exchangeRate = $request->exchange_rate;

                    if ($wallet->balance < $request->amount) {
                        return redirect()->back()->withErrors(['amount' => 'Insufficient wallet balance.'])->withInput();
                    }

                    // خصم المبلغ من المحفظة المصدر
                    $wallet->decrement('balance', $request->amount);

                    // حساب المبلغ المحول بعملة المحفظة الهدف
                    $convertedAmount = $request->amount * $exchangeRate;

                    // إضافة المبلغ إلى المحفظة الهدف
                    $relatedWallet->increment('balance', $convertedAmount);

                    // تسجيل معاملة تحويل خروج
                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'type' => 'transfer_out',
                        'amount' => $request->amount,
                        'description' => $request->description ?: 'Transfer to wallet ID ' . $relatedWallet->id,
                        'transaction_date' => $request->transaction_date,
                        'related_wallet_id' => $relatedWallet->id,
                        'exchange_rate' => $exchangeRate,
                    ]);

                    // تسجيل معاملة تحويل دخول
                    WalletTransaction::create([
                        'wallet_id' => $relatedWallet->id,
                        'type' => 'transfer_in',
                        'amount' => $convertedAmount,
                        'description' => 'Transfer received from wallet ID ' . $wallet->id,
                        'transaction_date' => $request->transaction_date,
                        'related_wallet_id' => $wallet->id,
                        'exchange_rate' => $exchangeRate,
                    ]);

                    DB::commit();

                    flash()->success('Transfer completed successfully.');
                    return redirect()->route('admin.wallet-transactions.index');
            }

            // لبقية أنواع المعاملات (مصروف، دخل، سحب، تمويل)
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => $request->type,
                'amount' => $request->amount,
                'description' => $request->description,
                'transaction_date' => $request->transaction_date,
                'related_wallet_id' => null,
                'exchange_rate' => null,
            ]);

            DB::commit();

            flash()->success('Transaction added successfully.');
            return redirect()->route('admin.wallet-transactions.index');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
        }
    }
    // الدوال الأخرى (show, edit, update, destroy) يمكن تركها فارغة أو تكملها حسب حاجتك.

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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
