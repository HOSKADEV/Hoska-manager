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
use PhpOffice\PhpSpreadsheet\Style\Color;


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
        $exchangeRate   = $invoice->project->exchange_rate ?? 1;
        $totalAmountDz  = $totalAmount * $exchangeRate;

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Invoice');

        // 1) إعداد الخط الافتراضي
        $spreadsheet->getDefaultStyle()->getFont()
            ->setName('Arial')->setSize(11);

        // 2) Header الشركة
        $sheet->mergeCells('B2:G2');
        $sheet->setCellValue('B2', 'EURL Hoska Dev');
        $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('B2')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('B3:G3');
        $sheet->setCellValue('B3', 'Rue Mohamed Khemisti - El Mousaaba, El Oued - Algeria');
        $sheet->getStyle('B3')->getFont()->setItalic(true)->setSize(10);
        $sheet->getStyle('B3')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 3) Project Financial Summary
        $sheet->mergeCells('B5:C5')->setCellValue('B5', 'Total Amount');
        $sheet->mergeCells('D5:E5')->setCellValue('D5', 'Paid Amount');
        $sheet->mergeCells('F5:G5')->setCellValue('F5', 'Remaining Amount');

        $sheet->mergeCells('B6:C6')->setCellValue('B6', number_format($totalAmount, 2) . ' ' . $invoice->project->currency);
        $sheet->mergeCells('D6:E6')->setCellValue('D6', number_format($paidAmount, 2)  . ' ' . $invoice->project->currency);
        $sheet->mergeCells('F6:G6')->setCellValue('F6', number_format($remainingAmount, 2) . ' ' . $invoice->project->currency);

        $sheet->mergeCells('B7:C7')->setCellValue('B7', 'Paid Percentage');
        $sheet->mergeCells('B8:C8')->setCellValue('B8', $paidPercentage . '%');

        foreach (['B5', 'D5', 'F5', 'B7'] as $cell) {
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('87CEEB');
            $sheet->getStyle($cell)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        foreach (['B6', 'D6', 'F6', 'B8'] as $cell) {
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $sheet->getStyle('B5:G8')->getBorders()
            ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // 4) سطر “Invoice Details” و “Submitted on”
        $sheet->mergeCells('B10:G10');
        $sheet->setCellValue('B10', 'Invoice Details');
        $sheet->getStyle('B10')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('B10')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFB6C1');
        $sheet->getStyle('B10')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('B11', 'Submitted on ' . $invoice->invoice_date->format('n/j/Y'));
        $sheet->getStyle('B11')->getFont()->setBold(true)->getColor()->setRGB('FF0000');

        // 5) Invoice Information (عمودي)
        $rowInfo = 12;
        $sheet->mergeCells("B{$rowInfo}:C{$rowInfo}");
        $sheet->setCellValue("B{$rowInfo}", 'Invoice Information');
        $sheet->getStyle("B{$rowInfo}")
            ->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle("B{$rowInfo}")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('6c757d');
        $sheet->getStyle("B{$rowInfo}")
            ->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("B{$rowInfo}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $info = [
            'Invoice Number' => $invoice->invoice_number,
            'Project'        => $invoice->project->name ?? '-',
            'Client'         => $invoice->client->name ?? '-',
            'Amount'         => number_format($invoice->amount, 2) . ' ' . $invoice->project->currency,
            'Is Paid'        => $invoice->is_paid ? 'Paid' : 'Unpaid',
            'Invoice Date'   => $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d H:i') : '-',
            'Due Date'       => $invoice->due_date ? $invoice->due_date->format('Y-m-d H:i') : '-',
            'Created At'     => $invoice->created_at->format('Y-m-d H:i'),
            'Updated At'     => $invoice->updated_at->format('Y-m-d H:i'),
        ];
        $row = $rowInfo + 1;
        foreach ($info as $label => $value) {
            $sheet->setCellValue("B{$row}", $label);
            $sheet->getStyle("B{$row}")->getFont()->setBold(true);
            $sheet->getStyle("B{$row}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->setCellValue("C{$row}", $value);
            $sheet->getStyle("C{$row}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $sheet->getStyle("B{$row}:C{$row}")
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            if ($label === 'Is Paid') {
                $sheet->getStyle("C{$row}")->getFont()
                    ->getColor()->setRGB($invoice->is_paid ? '008000' : 'FFA500');
            }
            $row++;
        }
        foreach (['B', 'C'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 6) جدول البنود يبدأ من الصف 20
        $start = 23;
        $sheet->mergeCells("B{$start}:D{$start}")->setCellValue("B{$start}", 'Description');
        $sheet->setCellValue("E{$start}", 'Qty');
        $sheet->setCellValue("F{$start}", 'Unit price');
        $sheet->setCellValue("G{$start}", 'Line Total');

        $hdr = "B{$start}:G{$start}";
        $sheet->getStyle($hdr)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($hdr)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FF69B4');
        $sheet->getStyle($hdr)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($hdr)->getBorders()
            ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach ($invoice->items as $i => $item) {
            $r = $start + 1 + $i;
            $sheet->mergeCells("B{$r}:D{$r}")->setCellValue("B{$r}", $item->description);
            $sheet->setCellValue("E{$r}", $item->quantity);
            $sheet->setCellValue("F{$r}", 'dz' . number_format($item->unit_price, 2));
            $line = $item->quantity * $item->unit_price * $exchangeRate;
            $sheet->setCellValue("G{$r}", 'dz' . number_format($line, 2));

            $rgn = "B{$r}:G{$r}";
            $sheet->getStyle($rgn)->getBorders()
                ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            if ($i % 2 === 0) {
                $sheet->getStyle($rgn)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFF0F5');
            }
        }

        // 7) ملخص الفاتورة بعد جدول البنود (نسق جميل)
        $f = $start + count($invoice->items) + 2;
        $currency         = $invoice->project->currency ?? '';
        $totalAmountDz    = $totalAmount    * $exchangeRate;
        $paidAmountDz     = $paidAmount     * $exchangeRate;
        $currentInvoiceDz = $invoice->amount * $exchangeRate;
        $remainingDz      = $totalAmountDz  - $paidAmountDz;

        // عنوان الملخص
        $sheet->mergeCells("F{$f}:G{$f}");
        $sheet->setCellValue("F{$f}", 'Invoice Summary');
        $sheet->getStyle("F{$f}:G{$f}")
            ->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("F{$f}:G{$f}")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D3D3D3');
        $sheet->getStyle("F{$f}:G{$f}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rows = [
            'Total'        => number_format($totalAmountDz,    2) . ' ' . $currency,
            'Paid Amount'  => number_format($paidAmountDz,     2) . ' ' . $currency,
            'This Invoice' => number_format($currentInvoiceDz, 2) . ' ' . $currency,
            'Remaining'    => number_format($remainingDz,      2) . ' ' . $currency,
        ];
        $line = $f + 1;
        foreach ($rows as $label => $value) {
            $sheet->setCellValue("F{$line}", $label . ':');
            $sheet->getStyle("F{$line}")->getFont()->setBold(true);
            $sheet->getStyle("F{$line}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->setCellValue("G{$line}", $value);
            $sheet->getStyle("G{$line}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $sheet->getStyle("F{$line}:G{$line}")
                ->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            if (($line - $f) % 2 === 0) {
                $sheet->getStyle("F{$line}:G{$line}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F5F5F5');
            }
            $line++;
        }
        foreach (['F', 'G'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 8) Notes + بيانات الشركة بأسفل الملف (نسق محسّن)
        // حدد رقم الصف الذي سينتهي عنده الملخص:
        $endSummaryRow = $line - 1;

        // صف الفاصلة قبل الملاحظات
        $dividerRow = $endSummaryRow + 2;
        $sheet->mergeCells("B{$dividerRow}:G{$dividerRow}");
        $sheet->setCellValue("B{$dividerRow}", '');
        $sheet->getStyle("B{$dividerRow}:G{$dividerRow}")
            ->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);

        // صندوق الملاحظات
        $notesRow = $dividerRow + 1;
        $sheet->mergeCells("B{$notesRow}:G" . ($notesRow + 1));
        $sheet->setCellValue("B{$notesRow}", 'Notes: This invoice is available for one month');

        // تهيئة الخط مائل
        $sheet->getStyle("B{$notesRow}:G" . ($notesRow + 1))
            ->getFont()->setItalic(true);

        // تهيئة المحاذاة مع تطبيق المسافة البادئة
        $sheet->getStyle("B{$notesRow}:G" . ($notesRow + 1))
            ->getAlignment()
            ->setIndent(1);

        $sheet->getStyle("B{$notesRow}:G" . ($notesRow + 1))
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFF9C4'); // أصفر فاتح
        $sheet->getStyle("B{$notesRow}:G" . ($notesRow + 1))
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // بيانات الشركة في صفين داخل صندوق منفصل
        $companyStart = $notesRow + 3;
        $sheet->mergeCells("B{$companyStart}:D{$companyStart}");
        $sheet->setCellValue("B{$companyStart}", 'R.C.N: 39/00-0544928B22');
        $sheet->mergeCells("B" . ($companyStart + 1) . ":D" . ($companyStart + 1));
        $sheet->setCellValue("B" . ($companyStart + 1), 'NIS: 002239010011172');
        $sheet->mergeCells("B" . ($companyStart + 2) . ":D" . ($companyStart + 2));
        $sheet->setCellValue("B" . ($companyStart + 2), 'NIF: 002239054492898');

        $sheet->mergeCells("E{$companyStart}:G{$companyStart}");
        $sheet->setCellValue("E{$companyStart}", 'Phone: +213 (0)774393983');
        $sheet->mergeCells("E" . ($companyStart + 1) . ":G" . ($companyStart + 1));
        $sheet->setCellValue("E" . ($companyStart + 1), 'Email: contact@hoskadev.com');
        $sheet->mergeCells("E" . ($companyStart + 2) . ":G" . ($companyStart + 2));
        $sheet->setCellValue("E" . ($companyStart + 2), 'Website: www.hoskadev.com');

        // تنسيق صندوق بيانات الشركة
        $sheet->getStyle("B{$companyStart}:G" . ($companyStart + 2))
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E3F2FD'); // أزرق فاتح
        $sheet->getStyle("B{$companyStart}:G" . ($companyStart + 2))
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // محاذاة نص بيانات الشركة
        foreach (range($companyStart, $companyStart + 2) as $r) {
            $sheet->getStyle("B{$r}:D{$r}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("E{$r}:G{$r}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        // 9) auto-size + إعداد الطباعة
        foreach (['B', 'C', 'D', 'E', 'F', 'G', 'H'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1);

        // 10) تصدير الملف
        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(
            fn() => $writer->save('php://output'),
            'invoice-' . $invoice->invoice_number . '.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }
}
