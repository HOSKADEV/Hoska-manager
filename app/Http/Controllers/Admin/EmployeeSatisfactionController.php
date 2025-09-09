<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeSatisfaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeSatisfactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $year = request('year', now()->year);
        $month = request('month', now()->month);

        // Get available years with data
        $availableYears = EmployeeSatisfaction::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->pluck('year')
            ->sortDesc()
            ->values();

        // Get available months for the selected year
        $availableMonths = EmployeeSatisfaction::selectRaw('MONTH(created_at) as month')
            ->whereYear('created_at', $year)
            ->distinct()
            ->pluck('month')
            ->sortDesc()
            ->values();

        // Get all employees
        $employees = Employee::with('user')->get();

        // Get satisfaction data for the selected month and year
        $satisfactionData = [];
        $overallAverages = [
            'salary_compensation' => 0,
            'work_environment' => 0,
            'colleagues_relationship' => 0,
            'management_relationship' => 0,
            'growth_opportunities' => 0,
            'work_life_balance' => 0,
            'overall_satisfaction' => 0
        ];

        foreach ($employees as $employee) {
            $satisfaction = EmployeeSatisfaction::where('employee_id', $employee->id)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            if ($satisfaction) {
                $satisfactionData[$employee->id] = [
                    'employee' => $employee,
                    'satisfaction' => $satisfaction,
                    'overall_score' => round((
                        $satisfaction->salary_compensation +
                        $satisfaction->work_environment +
                        $satisfaction->colleagues_relationship +
                        $satisfaction->management_relationship +
                        $satisfaction->growth_opportunities +
                        $satisfaction->work_life_balance
                    ) / 6)
                ];

                // Add to overall averages
                $overallAverages['salary_compensation'] += $satisfaction->salary_compensation;
                $overallAverages['work_environment'] += $satisfaction->work_environment;
                $overallAverages['colleagues_relationship'] += $satisfaction->colleagues_relationship;
                $overallAverages['management_relationship'] += $satisfaction->management_relationship;
                $overallAverages['growth_opportunities'] += $satisfaction->growth_opportunities;
                $overallAverages['work_life_balance'] += $satisfaction->work_life_balance;
                $overallAverages['overall_satisfaction'] += $satisfactionData[$employee->id]['overall_score'];
            } else {
                $satisfactionData[$employee->id] = [
                    'employee' => $employee,
                    'satisfaction' => null,
                    'overall_score' => 0
                ];
            }
        }

        // Calculate averages if we have data
        $count = count(array_filter($satisfactionData, function($item) {
            return $item['satisfaction'] !== null;
        }));

        if ($count > 0) {
            foreach ($overallAverages as $key => $value) {
                $overallAverages[$key] = round($value / $count);
            }
        }

        // Get monthly trend data for charts
        $monthlyTrend = [];
        $monthlyLabels = [];

        for ($m = 1; $m <= 12; $m++) {
            $monthlyLabels[] = date('M', mktime(0, 0, 0, $m, 1));

            $monthData = EmployeeSatisfaction::where('year', $year)
                ->where('month', $m)
                ->get();

            if ($monthData->count() > 0) {
                $monthlyTrend[$m] = [
                    'salary_compensation' => round($monthData->avg('salary_compensation')),
                    'work_environment' => round($monthData->avg('work_environment')),
                    'colleagues_relationship' => round($monthData->avg('colleagues_relationship')),
                    'management_relationship' => round($monthData->avg('management_relationship')),
                    'growth_opportunities' => round($monthData->avg('growth_opportunities')),
                    'work_life_balance' => round($monthData->avg('work_life_balance')),
                    'overall_satisfaction' => round((
                        $monthData->avg('salary_compensation') +
                        $monthData->avg('work_environment') +
                        $monthData->avg('colleagues_relationship') +
                        $monthData->avg('management_relationship') +
                        $monthData->avg('growth_opportunities') +
                        $monthData->avg('work_life_balance')
                    ) / 6)
                ];
            } else {
                $monthlyTrend[$m] = [
                    'salary_compensation' => 0,
                    'work_environment' => 0,
                    'colleagues_relationship' => 0,
                    'management_relationship' => 0,
                    'growth_opportunities' => 0,
                    'work_life_balance' => 0,
                    'overall_satisfaction' => 0
                ];
            }
        }

        return view('admin.employee-satisfaction.index', compact(
            'year',
            'month',
            'availableYears',
            'availableMonths',
            'satisfactionData',
            'overallAverages',
            'monthlyTrend',
            'monthlyLabels'
        ));
    }

    /**
     * Show the form for employees to submit their own satisfaction.
     */
    public function employeeForm()
    {
        $user = Auth::user();
        if ($user->type !== 'employee') {
            return redirect()->route('admin.index')
                ->with('error', 'Only employees can access this page.');
        }

        $employee = $user->employee;
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Check if employee already submitted for this month
        $existing = EmployeeSatisfaction::where('employee_id', $employee->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first();

        return view('admin.employee-satisfaction.employee-form', compact(
            'employee',
            'currentMonth',
            'currentYear',
            'existing'
        ));
    }

    /**
     * Store employee's own satisfaction submission.
     */
    public function employeeSubmit(Request $request)
    {
        $user = Auth::user();
        if ($user->type !== 'employee') {
            return redirect()->route('admin.index')
                ->with('error', 'Only employees can submit satisfaction data.');
        }

        $employee = $user->employee;
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $request->validate([
            'salary_compensation' => 'required|integer|min:1|max:10',
            'work_environment' => 'required|integer|min:1|max:10',
            'colleagues_relationship' => 'required|integer|min:1|max:10',
            'management_relationship' => 'required|integer|min:1|max:10',
            'growth_opportunities' => 'required|integer|min:1|max:10',
            'work_life_balance' => 'required|integer|min:1|max:10',
        ]);

        // Check if employee already submitted for this month
        $existing = EmployeeSatisfaction::where('employee_id', $employee->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first();

        if ($existing) {
            $existing->update($request->all());
            $message = 'Your satisfaction data has been updated successfully.';
        } else {
            EmployeeSatisfaction::create([
                'employee_id' => $employee->id,
                'month' => $currentMonth,
                'year' => $currentYear,
                'salary_compensation' => $request->salary_compensation,
                'work_environment' => $request->work_environment,
                'colleagues_relationship' => $request->colleagues_relationship,
                'management_relationship' => $request->management_relationship,
                'growth_opportunities' => $request->growth_opportunities,
                'work_life_balance' => $request->work_life_balance,
            ]);
            $message = 'Your satisfaction data has been submitted successfully.';
        }

        return redirect()->route('admin.index')
            ->with('success', $message);
    }

    /**
     * Toggle the force employee satisfaction setting
     */
    public function toggleForceSatisfaction(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean'
        ]);

        \App\Models\Setting::set('force_employee_satisfaction', $request->enabled, 'Force employees to submit satisfaction rating');

        return response()->json([
            'success' => true,
            'message' => $request->enabled 
                ? 'Force satisfaction rating has been enabled.' 
                : 'Force satisfaction rating has been disabled.'
        ]);
    }
}
