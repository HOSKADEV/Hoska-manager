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

        // حساب الإنفقات حسب الفترات الزمنية
        $now = now();

        // الإنفقات الساعية (آخر ساعة)
        $hourlyExpenses = WalletTransaction::where('type', 'expense')
            ->where('transaction_date', '>=', $now->subHour())
            ->sum('amount');

        // الإنفقات اليومية (اليوم الحالي)
        $dailyExpenses = WalletTransaction::where('type', 'expense')
            ->whereDate('transaction_date', $now->toDateString())
            ->sum('amount');

        // الإنفقات الأسبوعية (الأسبوع الحالي)
        $weeklyExpenses = WalletTransaction::where('type', 'expense')
            ->whereBetween('transaction_date', [$now->startOfWeek(), $now->endOfWeek()])
            ->sum('amount');

        // الإنفقات الشهرية (الشهر الحالي)
        $monthlyExpenses = WalletTransaction::where('type', 'expense')
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year)
            ->sum('amount');

        return view('admin.wallet_transactions.index', compact(
            'transactions',
            'wallets',
            'hourlyExpenses',
            'dailyExpenses',
            'weeklyExpenses',
            'monthlyExpenses'
        ));
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
            'type' => 'required|in:expense,income,transfer,withdraw,funding,sallary,assets',
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'related_wallet_id' => [
                'required_if:type,transfer',
                'nullable',
                'exists:wallets,id',
            ],
            'exchange_rate' => [
                'nullable',
                'numeric',
                'min:0.0001',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->type === 'transfer') {
                        $fromWallet = Wallet::find($request->wallet_id);
                        $toWallet   = Wallet::find($request->related_wallet_id);

                        // إذا كانت العملتين مختلفة و الحقل فارغ
                        if ($fromWallet && $toWallet && $fromWallet->currency !== $toWallet->currency && !$value) {
                            $fail('The exchange rate field is required when transferring between different currencies.');
                        }
                    }
                }
            ],
        ]);

        $wallet = Wallet::findOrFail($request->wallet_id);

        DB::beginTransaction();

        try {
            switch ($request->type) {
                case 'expense':
                case 'sallary':
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

                    // إذا نفس العملة → نضع 1 تلقائيًا
                    if ($wallet->currency === $relatedWallet->currency) {
                        $exchangeRate = 1;
                    }

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
        $transaction = WalletTransaction::findOrFail($id);
        $wallets = Wallet::all();

        // بدل with('currencyRelation') استخدم فقط all()
        $walletsFull = Wallet::all();

        $relatedTransaction = null;
        if ($transaction->type === 'transfer_out') {
            $relatedTransaction = WalletTransaction::where('type', 'transfer_in')
                ->where('wallet_id', $transaction->related_wallet_id)
                ->where('related_wallet_id', $transaction->wallet_id)
                ->where('transaction_date', $transaction->transaction_date)
                ->first();
        }

        return view('admin.wallet_transactions.edit', compact('transaction', 'wallets', 'walletsFull', 'relatedTransaction'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        $transaction = WalletTransaction::findOrFail($id);

        // dd($transaction);
        $request->validate([
            'type' => 'required|in:expense,income,transfer,withdraw,funding,sallary,assets',
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'related_wallet_id' => 'required_if:type,transfer|nullable|exists:wallets,id',
            'exchange_rate' => 'required_if:type,transfer|nullable|numeric|min:0.0001',
        ]);

        DB::beginTransaction();

        try {
            // تحقق من وجود المحافظ القديمة والجديدة
            $oldWallet = Wallet::find($transaction->wallet_id);
            if (!$oldWallet) {
                return back()->withErrors(['wallet_id' => 'Original wallet not found.'])->withInput();
            }
            $newWallet = Wallet::find($request->wallet_id);
            if (!$newWallet) {
                return back()->withErrors(['wallet_id' => 'Selected wallet not found.'])->withInput();
            }
            // إلغاء تأثير المعاملة القديمة على الرصيد
            switch ($transaction->type) {
                case 'expense':
                case 'sallary':
                case 'withdraw':
                    $oldWallet->increment('balance', $transaction->amount);
                    break;
                case 'income':
                case 'funding':
                    $oldWallet->decrement('balance', $transaction->amount);
                    break;
                case 'transfer_out':
                    $oldWallet->increment('balance', $transaction->amount);
                    $related = Wallet::find($transaction->related_wallet_id);
                    if ($related) {
                        $convertedAmount = $transaction->amount * $transaction->exchange_rate;
                        $related->decrement('balance', $convertedAmount);
                    }
                    break;
            }

            // حذف المعاملة المرتبطة (transfer_in) القديمة إذا كانت تحويل
            if ($transaction->type == 'transfer_out') {
                WalletTransaction::where('type', 'transfer_in')
                    ->where('wallet_id', $transaction->related_wallet_id)
                    ->where('related_wallet_id', $transaction->wallet_id)
                    ->where('transaction_date', $transaction->transaction_date)
                    ->delete();
            }

            // تنفيذ التحديث الجديد وتأثيره على الرصيد
            switch ($request->type) {
                case 'expense':
                case 'sallary':
                case 'withdraw':
                    if ($newWallet->balance < $request->amount) {
                        return back()->withErrors(['amount' => 'Insufficient wallet balance.'])->withInput();
                    }
                    $newWallet->decrement('balance', $request->amount);
                    break;

                case 'income':
                case 'funding':
                    $newWallet->increment('balance', $request->amount);
                    break;

                case 'transfer':
                    if ($request->wallet_id == $request->related_wallet_id) {
                        return back()->withErrors(['related_wallet_id' => 'Cannot transfer to the same wallet.'])->withInput();
                    }

                    $targetWallet = Wallet::find($request->related_wallet_id);
                    if (!$targetWallet) {
                        return back()->withErrors(['related_wallet_id' => 'Related wallet not found.'])->withInput();
                    }

                    $exchangeRate = $request->exchange_rate;

                    if ($newWallet->balance < $request->amount) {
                        return back()->withErrors(['amount' => 'Insufficient wallet balance.'])->withInput();
                    }

                    $convertedAmount = $request->amount * $exchangeRate;

                    $newWallet->decrement('balance', $request->amount);
                    $targetWallet->increment('balance', $convertedAmount);

                    // حذف سجل التحويل الداخل القديم المرتبط بهذه المعاملة قبل الإنشاء الجديد
                    WalletTransaction::where('type', 'transfer_in')
                        ->where('wallet_id', $targetWallet->id)
                        ->where('related_wallet_id', $newWallet->id)
                        ->where('transaction_date', $request->transaction_date)
                        ->delete();

                    // إنشاء سجل تحويل داخلي جديد
                    WalletTransaction::create([
                        'wallet_id' => $targetWallet->id,
                        'type' => 'transfer_in',
                        'amount' => $convertedAmount,
                        'description' => 'Transfer received from wallet ID ' . $newWallet->id,
                        'transaction_date' => $request->transaction_date,
                        'related_wallet_id' => $newWallet->id,
                        'exchange_rate' => $exchangeRate,
                    ]);
                    break;
            }
            // تحديث سجل المعاملة الحالي
            $transaction->update([
                'wallet_id' => $request->wallet_id,
                'type' => $request->type == 'transfer' ? 'transfer_out' : $request->type,
                'amount' => $request->amount,
                'description' => $request->description,
                'transaction_date' => $request->transaction_date,
                'related_wallet_id' => $request->type == 'transfer' ? $request->related_wallet_id : null,
                'exchange_rate' => $request->type == 'transfer' ? $request->exchange_rate : null,
            ]);


            DB::commit();

            flash()->success('Transaction updated successfully.');
            return redirect()->route('admin.wallet-transactions.index');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $transaction = WalletTransaction::findOrFail($id);

        DB::beginTransaction();

        try {
            $wallet = Wallet::findOrFail($transaction->wallet_id);

            switch ($transaction->type) {
                case 'expense':
                case 'withdraw':
                    // استرجاع الرصيد المخصوم
                    $wallet->increment('balance', $transaction->amount);
                    break;

                case 'income':
                case 'funding':
                    // خصم الرصيد المضاف
                    $wallet->decrement('balance', $transaction->amount);
                    break;

                case 'transfer_out':
                    // استرجاع المبلغ للمحفظة الأصل
                    $wallet->increment('balance', $transaction->amount);

                    // استرجاع المبلغ المحول من المحفظة المرتبطة
                    $relatedWallet = Wallet::find($transaction->related_wallet_id);
                    if ($relatedWallet) {
                        $convertedAmount = $transaction->amount * $transaction->exchange_rate;
                        $relatedWallet->decrement('balance', $convertedAmount);
                    }

                    // حذف معاملة التحويل الداخلة المرتبطة
                    WalletTransaction::where('type', 'transfer_in')
                        ->where('wallet_id', $transaction->related_wallet_id)
                        ->where('related_wallet_id', $wallet->id)
                        ->where('transaction_date', $transaction->transaction_date)
                        ->delete();

                    break;

                case 'transfer_in':
                    // في حال كان هناك سجل تحويل داخلي فقط (نادرًا)
                    $wallet->decrement('balance', $transaction->amount);
                    break;
            }

            $transaction->delete();

            DB::commit();

            flash()->success('Transaction deleted successfully.');
            return redirect()->route('admin.wallet-transactions.index');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()]);
        }
    }
}
