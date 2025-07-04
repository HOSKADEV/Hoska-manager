<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class WalletsController extends Controller
{
    /**
     * Display a listing of the wallets.
     */
    public function index()
    {
        $wallets = Wallet::all();
        return view('admin.wallets.index', compact('wallets'));
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
        $transactions = $wallet->transactions()->latest()->paginate(20); // هذا صحيح
        $payments = Payment::whereHas('invoice', function ($q) use ($wallet) {
            $q->where('wallet_id', $wallet->id);
        })->paginate(15);

        return view('admin.wallets.show', compact('wallet', 'transactions', 'payments'));
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
