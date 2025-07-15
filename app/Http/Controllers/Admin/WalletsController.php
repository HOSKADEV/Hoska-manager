<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletsController extends Controller
{
    /**
     * Display a listing of the wallets.
     */
    public function index()
    {
        $wallets = Wallet::all();

        $currencySymbols = [
            'USD' => '$',
            'EUR' => '€',
            'DZD' => 'DZ',
        ];

        // أسعار صرف ثابتة للتحويل إلى دينار جزائري (عدلها حسب بياناتك الحقيقية)
        $exchangeRatesToDZD = [
            'DZD' => 1,
            'USD' => 140, // مثال: 1 دولار = 140 دينار جزائري
            'EUR' => 150, // مثال: 1 يورو = 150 دينار جزائري
        ];

        // حساب مجموع الرصيد لكل عملة
        $totalsByCurrency = $wallets->groupBy('currency')->map(function ($group) {
            return $group->sum('balance');
        });

        // حساب مجموع كل المحافظ بالدينار الجزائري (محول حسب سعر الصرف)
        $totalInDZD = $wallets->reduce(function ($carry, $wallet) use ($exchangeRatesToDZD) {
            $rate = $exchangeRatesToDZD[$wallet->currency] ?? 1;
            return $carry + ($wallet->balance * $rate);
        }, 0);

        return view('admin.wallets.index', compact('wallets', 'totalsByCurrency', 'totalInDZD', 'currencySymbols'));
    }

    /**
     * Show the form for creating a new wallet.
     */
    public function create()
    {
        return view('admin.wallets.create');
    }

    /**
     * Store a newly created wallet in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'currency' => 'required|in:EUR,USD,DZD',
            'initial_amount' => 'required|numeric|min:0',
        ]);

        $wallet = Wallet::create([
            'name' => $request->name,
            'currency' => $request->currency,
            'balance' => $request->initial_amount,
            'notes' => $request->notes,
        ]);

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'funding',
            'amount' => $request->initial_amount,
            'description' => 'Initial funding',
            'transaction_date' => now(),
        ]);

        flash()->success('Wallet created successfully');
        return redirect()->route('admin.wallets.index');
    }

    /**
     * Display the specified wallet and its transactions.
     */
    public function show(Wallet $wallet)
    {
        $transactions = $wallet->transactions()->latest()->paginate(20);

        $payments = Payment::whereHas('invoice', function ($q) use ($wallet) {
            $q->where('wallet_id', $wallet->id);
        })->paginate(15);

        // الرصيد الداخل: income, funding, transfer_in
        $totalIn = $wallet->transactions()
            ->whereIn('type', ['income', 'funding', 'transfer_in'])
            ->sum('amount');

        // الرصيد الخارج: expense, withdraw, transfer_out
        $totalOut = $wallet->transactions()
            ->whereIn('type', ['expense', 'withdraw', 'transfer_out'])
            ->sum('amount');

        // مجموع الدفعات
        $totalPayments = Payment::whereHas('invoice', function ($q) use ($wallet) {
            $q->where('wallet_id', $wallet->id);
        })->sum(DB::raw('amount * IFNULL(exchange_rate, 1)'));

        return view('admin.wallets.show', compact(
            'wallet',
            'transactions',
            'payments',
            'totalIn',
            'totalOut',
            'totalPayments'
        ));
    }


    /**
     * Show the form for editing the specified wallet.
     */
    public function edit(Wallet $wallet)
    {
        return view('admin.wallets.edit', compact('wallet'));
    }

    /**
     * Update the specified wallet in storage.
     */
    public function update(Request $request, Wallet $wallet)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'currency' => 'required|in:EUR,USD,DZD',
        ]);

        $wallet->update($request->only(['name', 'currency', 'notes']));

        flash()->success('Wallet updated successfully');
        return redirect()->route('admin.wallets.index');
    }

    /**
     * Remove the specified wallet from storage.
     */
    public function destroy(Wallet $wallet)
    {
        $wallet->delete();

        flash()->success('Wallet deleted successfully');
        return redirect()->route('admin.wallets.index');
    }
}
