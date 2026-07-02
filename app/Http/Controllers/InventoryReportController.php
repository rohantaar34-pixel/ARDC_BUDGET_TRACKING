<?php

namespace App\Http\Controllers;

use App\Models\InventoryAssignment;
use App\Models\InventoryItem;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class InventoryReportController extends Controller
{
    private function reportData(): array
    {
        $items = InventoryItem::withCount('assignments')
            ->withSum('assignments as assigned_quantity', 'quantity_assigned')
            ->withSum('assignments as assigned_value', 'total_cost')
            ->orderBy('name')
            ->get();

        $assignments = InventoryAssignment::with(['inventoryItem', 'project'])
            ->latest()
            ->get();

        $summary = [
            'total_items' => $items->count(),
            'total_stock_quantity' => $items->sum('quantity'),
            'total_stock_value' => $items->sum(fn ($item) => $item->quantity * $item->unit_cost),
            'total_assigned_quantity' => $assignments->sum('quantity_assigned'),
            'total_assigned_value' => $assignments->sum('total_cost'),
            'low_stock_count' => $items->where('quantity', '<=', 5)->where('quantity', '>', 0)->count(),
            'out_of_stock_count' => $items->where('quantity', 0)->count(),
        ];

        return compact('items', 'assignments', 'summary');
    }

    public function exportExcel(): Response
    {
        $data = $this->reportData();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventory Report');

        foreach (['A' => 28, 'B' => 18, 'C' => 14, 'D' => 14, 'E' => 16, 'F' => 16, 'G' => 16] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'PROJECT INVENTORY REPORT');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', 'Generated: ' . now()->format('F d, Y'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 4;
        $summaryRows = [
            ['Total Items', $data['summary']['total_items'], 'Stock Quantity', $data['summary']['total_stock_quantity']],
            ['Stock Value', $data['summary']['total_stock_value'], 'Assigned Value', $data['summary']['total_assigned_value']],
            ['Low Stock', $data['summary']['low_stock_count'], 'Out of Stock', $data['summary']['out_of_stock_count']],
        ];

        foreach ($summaryRows as $summaryRow) {
            $sheet->fromArray($summaryRow, null, "A{$row}");
            $sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);
            $row++;
        }

        $row += 2;
        $sheet->setCellValue("A{$row}", 'CURRENT STOCK');
        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($this->sectionStyle());
        $row++;

        $headers = ['ITEM', 'CATEGORY', 'UNIT', 'UNIT COST', 'IN STOCK', 'STOCK VALUE', 'ASSIGNMENTS'];
        $sheet->fromArray($headers, null, "A{$row}");
        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($this->headerStyle());
        $row++;

        foreach ($data['items'] as $item) {
            $stockValue = $item->quantity * $item->unit_cost;
            $sheet->fromArray([
                $item->name,
                $item->category ?? '-',
                $item->unit,
                (float) $item->unit_cost,
                $item->quantity,
                $stockValue,
                $item->assignments_count,
            ], null, "A{$row}");
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('"PHP"#,##0.00');
            $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('"PHP"#,##0.00');
            $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($this->bodyStyle());
            $row++;
        }

        $row += 2;
        $sheet->setCellValue("A{$row}", 'ASSIGNMENT HISTORY');
        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($this->sectionStyle());
        $row++;

        $sheet->fromArray(['DATE', 'ITEM', 'PROJECT', 'QUANTITY', 'UNIT COST', 'TOTAL COST', 'ASSIGNED BY'], null, "A{$row}");
        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($this->headerStyle());
        $row++;

        foreach ($data['assignments'] as $assignment) {
            $sheet->fromArray([
                $assignment->created_at->format('Y-m-d'),
                $assignment->inventoryItem?->name ?? '-',
                $assignment->project?->name ?? '-',
                $assignment->quantity_assigned,
                (float) $assignment->unit_cost_at_assignment,
                (float) $assignment->total_cost,
                $assignment->assigned_by ?? '-',
            ], null, "A{$row}");
            $sheet->getStyle("E{$row}:F{$row}")->getNumberFormat()->setFormatCode('"PHP"#,##0.00');
            $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($this->bodyStyle());
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $this->filename('xlsx') . '"',
        ]);
    }

    public function exportPdf(): Response
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $pdf = new Dompdf($options);
        $pdf->loadHtml($this->buildHtml($this->reportData()));
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $this->filename('pdf') . '"',
        ]);
    }

    public function exportWord(): Response
    {
        $data = $this->reportData();
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection();
        $section->addText('PROJECT INVENTORY REPORT', ['bold' => true, 'size' => 16], ['alignment' => 'center']);
        $section->addText('Generated: ' . now()->format('F d, Y'), [], ['alignment' => 'center']);
        $section->addTextBreak();

        $section->addText('SUMMARY', ['bold' => true, 'size' => 12]);
        foreach ($data['summary'] as $label => $value) {
            $section->addText(str($label)->replace('_', ' ')->title() . ': ' . $this->formatSummaryValue($label, $value));
        }

        $section->addTextBreak();
        $section->addText('CURRENT STOCK', ['bold' => true, 'size' => 12]);
        $stockTable = $section->addTable(['borderSize' => 1, 'borderColor' => 'CCCCCC', 'cellMargin' => 80]);
        $this->addWordHeader($stockTable, ['Item', 'Category', 'Unit', 'Unit Cost', 'In Stock', 'Stock Value']);
        foreach ($data['items'] as $item) {
            $row = $stockTable->addRow();
            $row->addCell(2600)->addText($item->name);
            $row->addCell(1600)->addText($item->category ?? '-');
            $row->addCell(900)->addText($item->unit);
            $row->addCell(1300)->addText('PHP ' . number_format($item->unit_cost, 2));
            $row->addCell(1000)->addText((string) $item->quantity);
            $row->addCell(1400)->addText('PHP ' . number_format($item->quantity * $item->unit_cost, 2));
        }

        $section->addTextBreak();
        $section->addText('ASSIGNMENT HISTORY', ['bold' => true, 'size' => 12]);
        $assignmentTable = $section->addTable(['borderSize' => 1, 'borderColor' => 'CCCCCC', 'cellMargin' => 80]);
        $this->addWordHeader($assignmentTable, ['Date', 'Item', 'Project', 'Qty', 'Total']);
        foreach ($data['assignments'] as $assignment) {
            $row = $assignmentTable->addRow();
            $row->addCell(1300)->addText($assignment->created_at->format('Y-m-d'));
            $row->addCell(2200)->addText($assignment->inventoryItem?->name ?? '-');
            $row->addCell(2200)->addText($assignment->project?->name ?? '-');
            $row->addCell(700)->addText((string) $assignment->quantity_assigned);
            $row->addCell(1400)->addText('PHP ' . number_format($assignment->total_cost, 2));
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="' . $this->filename('docx') . '"',
        ]);
    }

    private function buildHtml(array $data): string
    {
        $stockRows = $data['items']->map(function ($item) {
            $stockValue = $item->quantity * $item->unit_cost;

            return '<tr><td>' . e($item->name) . '</td><td>' . e($item->category ?? '-') . '</td><td>' . e($item->unit) . '</td><td class="num">PHP ' . number_format($item->unit_cost, 2) . '</td><td class="num">' . number_format($item->quantity) . '</td><td class="num">PHP ' . number_format($stockValue, 2) . '</td></tr>';
        })->implode('');

        $assignmentRows = $data['assignments']->map(function ($assignment) {
            return '<tr><td>' . $assignment->created_at->format('Y-m-d') . '</td><td>' . e($assignment->inventoryItem?->name ?? '-') . '</td><td>' . e($assignment->project?->name ?? '-') . '</td><td class="num">' . number_format($assignment->quantity_assigned) . '</td><td class="num">PHP ' . number_format($assignment->total_cost, 2) . '</td><td>' . e($assignment->assigned_by ?? '-') . '</td></tr>';
        })->implode('');

        $summary = $data['summary'];

        return '<!doctype html><html><head><meta charset="utf-8"><style>
            body{font-family:DejaVu Sans,Arial,sans-serif;font-size:10px;color:#111}
            h1{text-align:center;font-size:18px;margin:0 0 4px} .date{text-align:center;color:#666;margin-bottom:20px}
            table{width:100%;border-collapse:collapse;margin-bottom:18px} th{background:#333;color:#fff}
            th,td{border:1px solid #ccc;padding:6px;text-align:left}.num{text-align:right}.summary td{width:25%}
            h2{font-size:12px;border-bottom:1px solid #111;padding-bottom:4px;margin-top:18px}
        </style></head><body>
            <h1>PROJECT INVENTORY REPORT</h1><div class="date">Generated: ' . now()->format('F d, Y') . '</div>
            <h2>Summary</h2>
            <table class="summary">
                <tr><td><strong>Total Items</strong></td><td>' . number_format($summary['total_items']) . '</td><td><strong>Stock Value</strong></td><td>PHP ' . number_format($summary['total_stock_value'], 2) . '</td></tr>
                <tr><td><strong>Assigned Value</strong></td><td>PHP ' . number_format($summary['total_assigned_value'], 2) . '</td><td><strong>Low Stock</strong></td><td>' . number_format($summary['low_stock_count']) . '</td></tr>
                <tr><td><strong>Out of Stock</strong></td><td>' . number_format($summary['out_of_stock_count']) . '</td><td><strong>Total Assigned Qty</strong></td><td>' . number_format($summary['total_assigned_quantity']) . '</td></tr>
            </table>
            <h2>Current Stock</h2><table><thead><tr><th>Item</th><th>Category</th><th>Unit</th><th>Unit Cost</th><th>In Stock</th><th>Stock Value</th></tr></thead><tbody>' . $stockRows . '</tbody></table>
            <h2>Assignment History</h2><table><thead><tr><th>Date</th><th>Item</th><th>Project</th><th>Qty</th><th>Total</th><th>Assigned By</th></tr></thead><tbody>' . $assignmentRows . '</tbody></table>
        </body></html>';
    }

    private function addWordHeader($table, array $headers): void
    {
        $row = $table->addRow();
        foreach ($headers as $header) {
            $row->addCell(null, ['bgColor' => '333333'])->addText($header, ['bold' => true, 'color' => 'FFFFFF']);
        }
    }

    private function formatSummaryValue(string $label, mixed $value): string
    {
        return str_contains($label, 'value') ? 'PHP ' . number_format($value, 2) : number_format($value);
    }

    private function filename(string $extension): string
    {
        return 'Inventory_Report_' . now()->format('Y-m-d') . '.' . $extension;
    }

    private function sectionStyle(): array
    {
        return [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '333333']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
    }

    private function headerStyle(): array
    {
        return [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '555555']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ];
    }

    private function bodyStyle(): array
    {
        return [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ];
    }
}
