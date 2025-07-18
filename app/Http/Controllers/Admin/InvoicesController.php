<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Wallet;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\StreamedResponse;


class InvoicesController extends Controller
{
    public function index()
    {
        $invoices = Invoice::all();
        return view('admin.invoices.index', compact('invoices'));
    }

    public function create()
    {
        $invoice = new Invoice();
        $projects = Project::all();
        $clients = Client::all();
        $wallets = Wallet::all(); // جلب المحافظ

        return view('admin.invoices.create', compact('invoice', 'projects', 'clients', 'wallets'));
    }

    public function show(Invoice $invoice)
    {
        $projects = Project::all(); // لو تحتاج للخيارات أو عرض بيانات المشروع
        $wallets = Wallet::all();

        // احسب بيانات المشروع المالية
        $paidAmount = $invoice->project->invoices()
            ->where('is_paid', true)
            ->sum('amount');
        $totalAmount = $invoice->project->total_amount;
        $remainingAmount = $totalAmount - $paidAmount;
        $paidPercentage = $totalAmount > 0 ? round(($paidAmount / $totalAmount) * 100, 2) : 0;

        return view('admin.invoices.show', compact('invoice', 'wallets', 'paidAmount', 'totalAmount', 'remainingAmount', 'paidPercentage'));
    }

    public function store(InvoiceRequest $request)
    {
        $data = $request->validated();

        // تعيين is_paid تلقائياً كـ false (غير مدفوعة)
        $data['is_paid'] = false;

        // تعيين wallet_id و project_id كما هو من الريكوست
        // $data['wallet_id'] = $request->wallet_id;
        $data['project_id'] = $request->project_id;

        $project = Project::with('client')->findOrFail($request->project_id);

        if (!$project->client) {
            return back()->withErrors(['project_id' => 'This project does not have an associated client.']);
        }

        $data['client_id'] = $project->client->id;

        // حساب المبلغ المدفوع مسبقاً للمشروع (الفواتير المدفوعة فقط)
        $paidAmount = $project->invoices()
            ->where('is_paid', true)
            ->sum('amount');

        // حساب المبلغ المتبقي
        $remaining = $project->total_amount - $paidAmount;

        // منع إدخال مبلغ أكبر من المتبقي
        if ($data['amount'] > $remaining) {
            return back()->withErrors(['amount' => "The entered amount ({$data['amount']}) exceeds the remaining amount of the project ({$remaining})."]);
        }

        Invoice::create($data);

        flash()->success('Invoice created successfully');
        return redirect()->route('admin.invoices.index');
    }

    public function edit(Invoice $invoice)
    {
        $projects = Project::all();
        $clients = Client::all();
        $wallets = Wallet::all(); // جلب المحافظ

        return view('admin.invoices.edit', compact('invoice', 'projects', 'clients', 'wallets'));
    }

    public function update(InvoiceRequest $request, Invoice $invoice)
    {
        $data = $request->validated();

        // $data['wallet_id'] = $request->wallet_id;
        $data['project_id'] = $request->project_id;

        // تعيين is_paid تلقائياً (مثلاً دايماً false أو تحافظ على القيمة الحالية)
        // إذا تريد تبقي القيمة كما هي دون تعديل:
        $data['is_paid'] = $invoice->is_paid;

        $project = Project::with('client')->findOrFail($request->project_id);

        if (!$project->client) {
            return back()->withErrors(['project_id' => 'This project does not have an associated client.']);
        }

        $data['client_id'] = $project->client->id;

        // حساب المبلغ المدفوع مع استثناء الفاتورة الحالية
        $paidAmount = $project->invoices()
            ->where('is_paid', true)
            ->where('id', '!=', $invoice->id)
            ->sum('amount');

        $remaining = $project->total_amount - $paidAmount;

        if ($data['amount'] > $remaining) {
            return back()->withErrors(['amount' => "The entered amount ({$data['amount']}) exceeds the remaining amount of the project ({$remaining})."]);
        }

        $invoice->update($data);

        flash()->success('Invoice updated successfully');
        return redirect()->route('admin.invoices.index');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        flash()->success('Invoice deleted successfully');
        return redirect()->route('admin.invoices.index');
    }

    public function getProjectFinancials(Project $project)
    {
        // فقط الفواتير المدفوعة is_paid = true
        $paidAmount = Invoice::where('project_id', $project->id)
            ->where('is_paid', true)
            ->sum('amount');

        $remaining = $project->total_amount - $paidAmount;

        return response()->json([
            'total' => number_format($project->total_amount, 2, '.', ''),
            'paid' => number_format($paidAmount, 2, '.', ''),
            'remaining' => number_format($remaining, 2, '.', ''),
            'currency' => $project->currency // ✅ أضفنا العملة هنا
        ]);
    }

    public function info($id)
    {
        $invoice = Invoice::with(['project.client'])->findOrFail($id);

        return response()->json([
            'client_name'   => $invoice->project?->client?->name ?? 'N/A',
            'project_name'  => $invoice->project?->name ?? 'N/A',
            'amount'        => $invoice->amount,
            'currency'      => $invoice->project?->currency ?? 'N/A',
        ]);
    }

    /**
     * Export invoice details to Excel.
     *
     * @param int $invoiceId
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportInvoiceExcel($invoiceId)
    {
        $invoice = Invoice::with(['project', 'client', 'payments'])->findOrFail($invoiceId);

        $totalAmount = $invoice->amount;
        $paidAmount = $invoice->payments->sum(fn($p) => $p->amount * ($p->exchange_rate ?? 1));
        $remainingAmount = $totalAmount - $paidAmount;
        $paidPercentage = $totalAmount > 0 ? round(($paidAmount / $totalAmount) * 100, 2) : 0;
        $currency = $invoice->project->currency ?? '';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Project Financial Summary Title
        $sheet->setCellValue('A1', 'Project Financial Summary');

        // تنسيق العنوان
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF007BFF'); // أزرق
        $sheet->getStyle('A1')->getFont()->getColor()->setARGB('FFFFFFFF'); // أبيض
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1:B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // عناوين الملخص
        $summaryLabels = ['Total Amount', 'Paid Amount', 'Remaining Amount', 'Paid Percentage'];
        $summaryValues = [
            number_format($totalAmount, 2) . ' ' . $currency,
            number_format($paidAmount, 2) . ' ' . $currency,
            number_format($remainingAmount, 2) . ' ' . $currency,
            $paidPercentage . '%'
        ];

        $row = 3;
        foreach ($summaryLabels as $index => $label) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->setCellValue("B{$row}", $summaryValues[$index]);

            // تنسيق العناوين والبيانات
            $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$row}:B{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $row++;
        }

        // بدء بيانات الفاتورة من الصف 8
        $startRow = 8;

        // عناوين جدول الفاتورة
        $headers = ['Invoice Number', 'Project', 'Client', 'Amount', 'Currency', 'Is Paid', 'Invoice Date', 'Due Date', 'Created At', 'Updated At'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue("{$col}{$startRow}", $header);
            // تنسيق العناوين
            $sheet->getStyle("{$col}{$startRow}")->getFont()->setBold(true);
            $sheet->getStyle("{$col}{$startRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCE5FF'); // لون فاتح أزرق
            $sheet->getStyle("{$col}{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("{$col}{$startRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $col++;
        }

        // بيانات الفاتورة - الصف التالي
        $dataRow = $startRow + 1;

        $data = [
            $invoice->invoice_number,
            $invoice->project->name ?? '-',
            $invoice->client->name ?? '-',
            $invoice->amount,
            $currency,
            $invoice->is_paid ? 'Yes' : 'No',
            $invoice->invoice_date?->format('Y-m-d') ?? '-',
            $invoice->due_date?->format('Y-m-d') ?? '-',
            $invoice->created_at->format('Y-m-d H:i'),
            $invoice->updated_at->format('Y-m-d H:i'),
        ];

        $col = 'A';
        foreach ($data as $value) {
            $sheet->setCellValue("{$col}{$dataRow}", $value);
            $sheet->getStyle("{$col}{$dataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("{$col}{$dataRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $col++;
        }

        // ضبط عرض الأعمدة تلقائياً
        foreach (range('A', $col) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // حفظ الملف وارساله للمتصفح
        $writer = new Xlsx($spreadsheet);

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $filename = 'invoice-' . $invoice->invoice_number . '.xlsx';

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', "attachment;filename=\"$filename\"");
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
