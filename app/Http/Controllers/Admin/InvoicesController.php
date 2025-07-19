<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Wallet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Style\Font;


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

    private function calculateProjectSummary($invoice)
    {
        $paidAmount = $invoice->project->invoices()
            ->where('is_paid', true)
            ->sum('amount');
        $totalAmount = $invoice->project->total_amount;
        $remainingAmount = $totalAmount - $paidAmount;
        $paidPercentage = $totalAmount > 0 ? round(($paidAmount / $totalAmount) * 100, 2) : 0;

        return compact('paidAmount', 'totalAmount', 'remainingAmount', 'paidPercentage');
    }

    public function show(Invoice $invoice)
    {
        $projects = Project::all(); // حسب الحاجة
        $wallets = Wallet::all();

        extract($this->calculateProjectSummary($invoice));

        return view('admin.invoices.show', compact('invoice', 'wallets', 'paidAmount', 'totalAmount', 'remainingAmount', 'paidPercentage'));
    }

    private function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $year = now()->format('Y');
        $month = now()->format('m');

        $count = \App\Models\Invoice::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        $serial = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return "$prefix-$year-$month-$serial";
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

        // توليد رقم الفاتورة
        $data['invoice_number'] = $this->generateInvoiceNumber();

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

        // تحقق من عدم ترك رقم الفاتورة فارغاً (يمكن تعديل هذا حسب رغبتك)
        if (empty($data['invoice_number'])) {
            return back()->withErrors(['invoice_number' => 'Invoice number cannot be empty.']);
        }

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

    public function exportInvoiceExcel($invoiceId)
    {
        $invoice = Invoice::with(['project', 'client', 'items'])->findOrFail($invoiceId);

        extract($this->calculateProjectSummary($invoice));

        $exchangeRate = $invoice->project->exchange_rate ?? 1;
        $totalAmountDz = $totalAmount * $exchangeRate;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Invoice');

        // إعداد الخط الأساسي
        $defaultFont = $spreadsheet->getDefaultStyle()->getFont();
        $defaultFont->setName('Arial')->setSize(11);

        // العنوان الرئيسي للشركة
        $sheet->mergeCells('B2:G2');
        $sheet->setCellValue('B2', 'EURL Hoska Dev');
        $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // عنوان الفاتورة
        $sheet->mergeCells('B4:G4');
        $sheet->setCellValue('B4', 'Invoice Details');
        $sheet->getStyle('B4')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('B4')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFB6C1'); // وردي فاتح
        $sheet->getStyle('B4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // بيانات الفاتورة الأساسية
        $basicInfoLabels = [
            'B6' => 'Invoice Number:',
            'B7' => 'Project:',
            'B8' => 'Client:',
            'B9' => 'Invoice Date:',
            'B10' => 'Due Date:',
            'B11' => 'Status:',
        ];
        $basicInfoValues = [
            'C6' => $invoice->invoice_number,
            'C7' => $invoice->project->name ?? '-',
            'C8' => $invoice->client->name ?? '-',
            'C9' => $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : '-',
            'C10' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '-',
            'C11' => $invoice->is_paid ? 'Paid' : 'Unpaid',
        ];

        foreach ($basicInfoLabels as $cell => $text) {
            $sheet->setCellValue($cell, $text);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }
        foreach ($basicInfoValues as $cell => $value) {
            $sheet->setCellValue($cell, $value);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        // تلوين الحالة (Paid / Unpaid)
        $statusCell = 'C11';
        if ($invoice->is_paid) {
            $sheet->getStyle($statusCell)->getFont()->getColor()->setRGB('008000'); // أخضر
        } else {
            $sheet->getStyle($statusCell)->getFont()->getColor()->setRGB('FFA500'); // برتقالي
        }

        // ملخص المشروع المالي
        $sheet->mergeCells('E4:G4');
        $sheet->setCellValue('E4', 'Project Financial Summary');
        $sheet->getStyle('E4')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('E4')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('87CEEB'); // أزرق فاتح
        $sheet->getStyle('E4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $summaryLabels = [
            'E6' => 'Total Amount:',
            'E7' => 'Paid Amount:',
            'E8' => 'Remaining Amount:',
            'E9' => 'Paid Percentage:',
        ];
        $summaryValues = [
            'F6' => number_format($totalAmount, 2) . ' ' . ($invoice->project->currency ?? ''),
            'F7' => number_format($paidAmount, 2) . ' ' . ($invoice->project->currency ?? ''),
            'F8' => number_format($remainingAmount, 2) . ' ' . ($invoice->project->currency ?? ''),
            'F9' => $paidPercentage . '%',
        ];

        foreach ($summaryLabels as $cell => $text) {
            $sheet->setCellValue($cell, $text);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }
        foreach ($summaryValues as $cell => $value) {
            $sheet->setCellValue($cell, $value);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        // جدول البنود - العناوين
        $startRow = 13;
        $sheet->setCellValue("B{$startRow}", 'Description');
        $sheet->setCellValue("E{$startRow}", 'Quantity');
        $sheet->setCellValue("F{$startRow}", 'Unit Price');
        $sheet->setCellValue("G{$startRow}", 'Line Total');

        // تنسيق رأس الجدول
        $headerRange = "B{$startRow}:G{$startRow}";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FF69B4'); // وردي
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // تعبئة البنود
        $itemsStartRow = $startRow + 1;
        $totalItems = 0;
        foreach ($invoice->items as $i => $item) {
            $row = $itemsStartRow + $i;
            $sheet->setCellValue("B{$row}", $item->description);
            $sheet->setCellValue("E{$row}", $item->quantity);
            $sheet->setCellValue("F{$row}", number_format($item->unit_price, 2));
            $lineTotal = $item->quantity * $item->unit_price;
            $sheet->setCellValue("G{$row}", 'dz' . number_format($lineTotal * $exchangeRate, 2));
            $totalItems += $lineTotal;

            // حدود الصف
            $sheet->getStyle("B{$row}:G{$row}")
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            // تظليل الصفوف الزوجية قليلاً لتحسين القراءة
            if ($i % 2 == 0) {
                $sheet->getStyle("B{$row}:G{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFF0F5'); // وردي فاتح جداً
            }
        }

        // إجمالي البنود بالعملة الأصلية (للمقارنة)
        $totalRow = $itemsStartRow + count($invoice->items) + 1;
        $sheet->setCellValue("F{$totalRow}", 'Total Items (original):');
        $sheet->setCellValue("G{$totalRow}", number_format($totalItems, 2) . ' ' . ($invoice->project->currency ?? ''));
        $sheet->getStyle("F{$totalRow}:G{$totalRow}")->getFont()->setBold(true);
        $sheet->getStyle("F{$totalRow}:G{$totalRow}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);

        // الإجمالي بعد تحويل العملة (المبلغ الحقيقي)
        $totalRow2 = $totalRow + 1;
        $sheet->setCellValue("F{$totalRow2}", 'Total (DZ):');
        $sheet->setCellValue("G{$totalRow2}", 'dz' . number_format($totalAmountDz, 2));
        $sheet->getStyle("F{$totalRow2}:G{$totalRow2}")->getFont()->setBold(true);
        $sheet->getStyle("F{$totalRow2}:G{$totalRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("F{$totalRow2}:G{$totalRow2}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);

        // ضبط عرض الأعمدة تلقائياً
        foreach (['B', 'C', 'D', 'E', 'F', 'G'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // تفعيل لف الصفحات للعرض أو الطباعة
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setFitToWidth(1);

        // تحميل الملف
        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'invoice-' . $invoice->invoice_number . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
