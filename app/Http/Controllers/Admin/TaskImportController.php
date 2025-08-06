<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Timesheet;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

class TaskImportController extends Controller
{
    public function import(Request $request)
    {
        // Validate that an Excel file (.xlsx) is uploaded
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx'
        ]);

        $file = $request->file('excel_file');
        // Load the spreadsheet file
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        // Convert sheet data to an array
        $rows = $sheet->toArray(null, true, true, true);

        // Convert headers to lowercase for consistency
        $headers = array_map('strtolower', $rows[1]);
        unset($rows[1]); // Remove header row from data rows

        foreach ($rows as $row) {
            // Combine headers with row values into an associative array
            $data = array_combine($headers, $row);

            // Find project by project_name column
            $project = Project::where('name', $data['project_name'])->first();
            // Find employee by employee_email column
            $employee = User::where('email', $data['employee_email'])->first();

            // Skip if project or employee not found
            if (!$project || !$employee) {
                continue;
            }

            try {
                // Create a new task with the imported data
                $task = Task::create([
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'status' => $data['status'] ?? 'pending',
                    'due_date' => $data['due_date'] ?? null,
                    'budget_amount' => $data['budget_amount'] ?? null,
                    'project_id' => $project->id,
                    'employee_id' => $employee->id,
                    'start_time' => $data['start_time'] ?? now(),
                    'end_time' => $data['end_time'] ?? null,
                ]);

                // تحديث التايم شيت بعد إضافة التاسك
                Timesheet::updateMonthlyTimesheet($employee->id, $task->start_time);
                
            } catch (\Exception $e) {
                // Log any errors occurred during import
                Log::error('Error importing task: ' . $e->getMessage());
            }
        }

        // Redirect back with a success message
        return back()->with('success', 'Tasks imported successfully');
    }
}
