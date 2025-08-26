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

        // Annual totals
        $annualIncome = Invoice::whereYear('invoice_date', $year)
            ->where('is_paid', 1)
            ->sum('amount');

        $annualExpenses = WalletTransaction::whereYear('transaction_date', $year)
            ->whereIn('type', ['expense', 'withdraw'])
            ->sum('amount');

        $annualSalaries = Timesheet::whereYear('work_date', $year)
            ->sum('month_salary');

        $annualProfits = $annualIncome - $annualExpenses - $annualSalaries;

        // Customer satisfaction score
        $csatScore = 85.0;

        // Monthly data for charts
        $monthsLabels = [];
        $monthlyIncomeData = [];
        $monthlyExpensesData = [];
        $monthlyProfitsData = [];
        $monthlyProjectsData = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthsLabels[] = date('F', mktime(0, 0, 0, $month, 1));

            // Monthly income
            $income = Invoice::whereYear('invoice_date', $year)
                ->whereMonth('invoice_date', $month)
                ->where('is_paid', 1)
                ->sum('amount');
            $monthlyIncomeData[] = $income;

            // Monthly expenses
            $expenses = WalletTransaction::whereYear('transaction_date', $year)
                ->whereMonth('transaction_date', $month)
                ->whereIn('type', ['expense', 'withdraw'])
                ->sum('amount');
            $monthlyExpensesData[] = $expenses;

            // Monthly salaries
            $salaries = Timesheet::whereYear('work_date', $year)
                ->whereMonth('work_date', $month)
                ->sum('month_salary');

            // Monthly profits
            $monthlyProfitsData[] = $income - $expenses - $salaries;

            // Monthly projects count directly from Project model
            $monthlyProjectsData[] = Project::whereYear('start_date', $year)
                ->whereMonth('start_date', $month)
                ->count();
        }

        return view('admin.kpis.index', compact(
            'annualIncome',
            'annualExpenses',
            'annualSalaries',
            'annualProfits',
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
