<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Timesheet;
use App\Models\WalletTransaction;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KpiController extends Controller
{
    // public function index()
    // {
    //     $year = Carbon::now()->year;

    //     // جلب بيانات المداخيل
    //     $monthlyIncome = DB::table('invoices')
    //         ->select(DB::raw('MONTH(invoice_date) as month'), DB::raw('SUM(amount) as total'))
    //         ->whereYear('invoice_date', $year)
    //         ->groupBy('month')
    //         ->orderBy('month')
    //         ->pluck('total', 'month')
    //         ->toArray();

    //     $incomeData = [];
    //     for ($m = 1; $m <= 12; $m++) {
    //         $incomeData[] = $monthlyIncome[$m] ?? 0;
    //     }

    //     // جلب بيانات المصاريف
    //     $monthlyExpenses = DB::table('wallet_transactions')
    //         ->select(DB::raw('MONTH(transaction_date) as month'), DB::raw('SUM(amount) as total'))
    //         ->whereYear('transaction_date', $year)
    //         ->whereIn('type', ['expense', 'withdraw'])
    //         ->groupBy('month')
    //         ->orderBy('month')
    //         ->pluck('total', 'month')
    //         ->toArray();

    //     $expensesData = [];
    //     for ($m = 1; $m <= 12; $m++) {
    //         $expensesData[] = $monthlyExpenses[$m] ?? 0;
    //     }

    //     // جلب بيانات ساعات العمل
    //     $monthlyHours = DB::table('timesheets')
    //         ->select(DB::raw('MONTH(work_date) as month'), DB::raw('SUM(hours_worked) as total'))
    //         ->whereYear('work_date', $year)
    //         ->groupBy('month')
    //         ->orderBy('month')
    //         ->pluck('total', 'month')
    //         ->toArray();

    //     $hoursData = [];
    //     for ($m = 1; $m <= 12; $m++) {
    //         $hoursData[] = $monthlyHours[$m] ?? 0;
    //     }

    //     // حساب الأرباح (مثال بمعدل ساعة ثابت)
    //     $hourlyRate = 20;
    //     $monthlySalaries = array_map(fn($hours) => $hours * $hourlyRate, $hoursData);

    //     $profitsData = [];
    //     for ($i = 0; $i < 12; $i++) {
    //         $profitsData[] = $incomeData[$i] - $expensesData[$i] - $monthlySalaries[$i];
    //     }

    //     $monthsLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    //     return view('admin.kpis.index', compact('monthsLabels', 'incomeData', 'expensesData', 'hoursData', 'profitsData'));
    // }
    public function index()
    {
        $year = request('year', now()->year);

        // Get available years with data
        $availableYears = collect();

        // Get years from invoices
        $invoiceYears = Invoice::selectRaw('YEAR(invoice_date) as year')
            ->distinct()
            ->pluck('year');

        // Get years from wallet transactions
        $transactionYears = WalletTransaction::selectRaw('YEAR(transaction_date) as year')
            ->distinct()
            ->pluck('year');

        // Get years from timesheets
        $timesheetYears = Timesheet::selectRaw('YEAR(work_date) as year')
            ->distinct()
            ->pluck('year');

        // Get years from projects
        $projectYears = Project::selectRaw('YEAR(start_date) as year')
            ->distinct()
            ->pluck('year');

        // Merge all years and sort in descending order
        $availableYears = $invoiceYears->merge($transactionYears)
            ->merge($timesheetYears)
            ->merge($projectYears)
            ->unique()
            ->sortDesc()
            ->values();

        // Annual totals by currency
        $annualIncomeByCurrency = [];
        $annualExpensesByCurrency = [];

        // Get income by currency from invoices (through wallets)
        $invoices = Invoice::whereYear('invoice_date', $year)
            ->where('is_paid', 1)
            ->with('wallet')
            ->get();

        foreach ($invoices as $invoice) {
            $currency = $invoice->wallet ? $invoice->wallet->currency : 'DZD';
            if (!isset($annualIncomeByCurrency[$currency])) {
                $annualIncomeByCurrency[$currency] = 0;
            }
            $annualIncomeByCurrency[$currency] += $invoice->amount;
        }

        // Get expenses by currency from wallet transactions
        $transactions = WalletTransaction::whereYear('transaction_date', $year)
            ->whereIn('type', ['expense'])
            // ->whereIn('type', ['expense', 'withdraw'])
            ->with('wallet')
            ->get();

        foreach ($transactions as $transaction) {
            $currency = $transaction->wallet ? $transaction->wallet->currency : 'DZD';
            if (!isset($annualExpensesByCurrency[$currency])) {
                $annualExpensesByCurrency[$currency] = 0;
            }
            $annualExpensesByCurrency[$currency] += $transaction->amount;
        }

        // Get salaries (assuming they're in DZD)
        $annualSalaries = Timesheet::whereYear('work_date', $year)
            ->sum('month_salary');

        // Convert all amounts to DZD using convertCurrency function
        $annualIncomeInDZD = 0;
        foreach ($annualIncomeByCurrency as $currency => $amount) {
            $annualIncomeInDZD += $this->convertCurrency($amount, $currency, 'DZD');
        }

        $annualExpensesInDZD = 0;
        foreach ($annualExpensesByCurrency as $currency => $amount) {
            $annualExpensesInDZD += $this->convertCurrency($amount, $currency, 'DZD');
        }

        $annualProfitsInDZD = $annualIncomeInDZD - $annualExpensesInDZD - $annualSalaries;

        // Customer satisfaction score
        $csatScore = 85.0;

        // Monthly data for charts
        $monthsLabels = [];
        $monthlyIncomeData = [];
        $monthlyExpensesData = [];
        $monthlyProfitsData = [];
        $monthlyProjectsData = [];
        $monthlyIncomeByCurrency = [];
        $monthlyExpensesByCurrency = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthsLabels[] = date('F', mktime(0, 0, 0, $month, 1));

            // Initialize monthly currency arrays
            $monthlyIncomeByCurrency[$month] = [];
            $monthlyExpensesByCurrency[$month] = [];

            // Monthly income by currency
            $monthlyInvoices = Invoice::whereHas('payments', function($query) use ($year, $month) {
                    $query->whereYear('payment_date', $year)
                        ->whereMonth('payment_date', $month);
                })
                ->where('is_paid', 1)
                ->with(['wallet', 'payments'])
                ->get();

            foreach ($monthlyInvoices as $invoice) {
                $currency = $invoice->wallet ? $invoice->wallet->currency : 'DZD';
                if (!isset($monthlyIncomeByCurrency[$month][$currency])) {
                    $monthlyIncomeByCurrency[$month][$currency] = 0;
                }
                $monthlyIncomeByCurrency[$month][$currency] += $invoice->amount;
            }

            // Monthly expenses by currency
            $monthlyTransactions = WalletTransaction::whereYear('transaction_date', $year)
                ->whereMonth('transaction_date', $month)
                ->whereIn('type', ['expense'])
                // ->whereIn('type', ['expense', 'withdraw'])
                ->with('wallet')
                ->get();

            foreach ($monthlyTransactions as $transaction) {
                $currency = $transaction->wallet ? $transaction->wallet->currency : 'DZD';
                if (!isset($monthlyExpensesByCurrency[$month][$currency])) {
                    $monthlyExpensesByCurrency[$month][$currency] = 0;
                }
                $monthlyExpensesByCurrency[$month][$currency] += $transaction->amount;
            }

            // Convert to DZD and add to monthly data using convertCurrency function
            $monthlyIncomeInDZD = 0;
            foreach ($monthlyIncomeByCurrency[$month] as $currency => $amount) {
                $monthlyIncomeInDZD += $this->convertCurrency($amount, $currency, 'DZD');
            }
            $monthlyIncomeData[] = $monthlyIncomeInDZD;

            $monthlyExpensesInDZD = 0;
            foreach ($monthlyExpensesByCurrency[$month] as $currency => $amount) {
                $monthlyExpensesInDZD += $this->convertCurrency($amount, $currency, 'DZD');
            }
            $monthlyExpensesData[] = $monthlyExpensesInDZD;

            // Monthly salaries (assuming they're in DZD)
            $salaries = Timesheet::whereYear('work_date', $year)
                ->whereMonth('work_date', $month)
                ->sum('month_salary');

            // Monthly profits
            $monthlyProfitsData[] = $monthlyIncomeInDZD - $monthlyExpensesInDZD - $salaries;

            // Monthly projects count directly from Project model
            $monthlyProjectsData[] = Project::whereYear('start_date', $year)
                ->whereMonth('start_date', $month)
                ->count();
        }

        return view('admin.kpis.index', compact(
            'annualIncomeInDZD',
            'annualExpensesInDZD',
            'annualSalaries',
            'annualProfitsInDZD',
            'csatScore',
            'monthsLabels',
            'monthlyIncomeData',
            'monthlyExpensesData',
            'monthlyProfitsData',
            'monthlyProjectsData',
            'year',
            'availableYears'
        ));
    }
}
