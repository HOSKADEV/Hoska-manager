<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Timesheet;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                $currency = $invoice->currency ? $invoice->currency : 'DZD';
                if (!isset($annualIncomeByCurrency[$currency])) {
                    $annualIncomeByCurrency[$currency] = 0;
                }
                $annualIncomeByCurrency[$currency] += $invoice->amount;
                // Log::info($invoice->currency);
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
        // $annualSalaries = Timesheet::whereYear('work_date', $year)
        //     ->sum('month_salary');
        $annualSalaries = WalletTransaction::where('type','sallary')->with('wallet')->sum('amount');
        // Convert all amounts to DZD using convertCurrency function
        $annualIncomeInDZD = 0;
        foreach ($annualIncomeByCurrency as $currency => $amount) {
            $annualIncomeInDZD += $this->convertCurrency($amount, $currency, 'DZD');
        }


        $annualExpensesInDZD = 0;
        foreach ($annualExpensesByCurrency as $currency => $amount) {
            $annualExpensesInDZD += $this->convertCurrency($amount, $currency, 'DZD');
        }

        $annualProfitsInDZD = $annualIncomeInDZD - ($annualExpensesInDZD + $annualSalaries);

        // Customer satisfaction score based on client projects and developments
        // Get all clients with their projects and developments
        $clients = Client::with(['projects', 'projects.developments'])->get();

        $totalClients = $clients->count();
        $repeatClients = 0;
        $totalProjects = 0;
        $totalDevelopments = 0;
        $clientsWithDevelopments = 0;

        foreach ($clients as $client) {
            $projectCount = $client->projects->count();
            $totalProjects += $projectCount;

            // Count clients with more than one project (repeat clients)
            if ($projectCount > 1) {
                $repeatClients++;
            }

            // Count developments and clients with developments
            foreach ($client->projects as $project) {
                $developmentCount = $project->developments->count();
                $totalDevelopments += $developmentCount;

                if ($developmentCount > 0) {
                    $clientsWithDevelopments++;
                }
            }
        }

        // Calculate CSAT score based on:
        // 1. Percentage of repeat clients (clients with multiple projects)
        // 2. Percentage of clients with developments
        // Base score starts at 70, then we add up to 30 points based on the metrics

        $csatScore = 70; // Base score

        if ($totalClients > 0) {
            // Add up to 15 points for repeat clients percentage
            $repeatClientPercentage = ($repeatClients / $totalClients) * 100;
            $csatScore += ($repeatClientPercentage / 100) * 15;

            // Add up to 15 points for clients with developments percentage
            $developmentsPercentage = ($clientsWithDevelopments / $totalClients) * 100;
            $csatScore += ($developmentsPercentage / 100) * 15;
        }

        // Ensure the score doesn't exceed 100
        $csatScore = min($csatScore, 100.0);

        // Monthly data for charts
        $monthsLabels = [];
        $monthlyIncomeData = [];
        $monthlyExpensesData = [];
        $monthlyProfitsData = [];
        $monthlyProjectsData = [];
        $monthlyIncomeByCurrency = [];
        $monthlyExpensesByCurrency = [];
        $monthlySalaryData = [];
        $monthlySalaryByCurrency = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthsLabels[] = date('F', mktime(0, 0, 0, $month, 1));

            // Initialize monthly currency arrays
            $monthlyIncomeByCurrency[$month] = [];
            $monthlyExpensesByCurrency[$month] = [];
            $monthlySalaryByCurrency[$month] = [];

            // Monthly income by currency
            $monthlyInvoices = Invoice::whereHas('payments', function($query) use ($year, $month) {
                    $query->whereYear('payment_date', $year)
                        ->whereMonth('payment_date', $month);
                })
                ->where('is_paid', 1)
                ->with(['wallet', 'payments'])
                ->get();

            foreach ($monthlyInvoices as $invoice) {
                $currency = $invoice->project ? $invoice->currency : 'DZD';
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

            // Monthly salary transactions by currency
            $monthlySalaryTransactions = WalletTransaction::whereYear('transaction_date', $year)
                ->whereMonth('transaction_date', $month)
                ->where('type', 'sallary') // Note: there's a typo in the migration
                ->with('wallet')
                ->get();

            foreach ($monthlySalaryTransactions as $transaction) {
                $currency = $transaction->wallet ? $transaction->wallet->currency : 'DZD';
                if (!isset($monthlySalaryByCurrency[$month][$currency])) {
                    $monthlySalaryByCurrency[$month][$currency] = 0;
                }
                $monthlySalaryByCurrency[$month][$currency] += $transaction->amount;
            }

            $monthlySalaryInDZD = 0;
            foreach ($monthlySalaryByCurrency[$month] as $currency => $amount) {
                $monthlySalaryInDZD += $this->convertCurrency($amount, $currency, 'DZD');
            }
            $monthlySalaryData[] = $monthlySalaryInDZD;

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

        // Get project profits data for the selected month (default to current month)
        $selectedMonth = request('month', now()->month);

        // Get projects with profits for the selected month
        $projectProfits = [];
        $projectsWithProfits = Project::whereYear('start_date', $year)
            ->whereMonth('start_date', $selectedMonth)
            ->get()
            ->map(function ($project) {
                $totalAmount = $this->convertCurrency($project->total_amount_project_with_developments, $project->currency, 'DZD');
                $totalIncome = $this->convertCurrency($project->total_paid_amount_project_with_developments, $project->currency, 'DZD');
                $totalExpenses = $this->convertCurrency($project->total_expenses, $project->currency, 'DZD');
                $profit = $totalIncome - $totalExpenses;
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'totalAmount' => $totalAmount,
                    'income' => $totalIncome,
                    'expenses' => $totalExpenses,
                    'profit' => $profit
                ];
            })
            ->values();

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
            'monthlySalaryData',
            'year',
            'availableYears',
            'projectsWithProfits',
            'selectedMonth'
        ));
    }
}
