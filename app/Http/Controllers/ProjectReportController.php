<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class ProjectReportController extends Controller
{
    private const FORMULA_PREFIX_PATTERN = '/^[=+\-@]/';

    private function reportData(Project $project): array
    {
        $transactions = $project->transactions()
            ->with('expenseCategory')
            ->orderBy('transaction_date')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $budgetAdditions = $transactions->where('type', 'budget_addition');
        $expenses = $transactions->where('type', 'expense');

        $categorySummary = $expenses
            ->groupBy(fn ($transaction) => $transaction->expenseCategory?->name ?? $transaction->category ?? 'Uncategorized')
            ->map(fn ($group) => (float) $group->sum('amount'))
            ->sortByDesc(fn ($amount) => $amount);

        $reservedProcurement = (float) $project->reserved_procurement;
        $totalBudget = (float) $project->budget + (float) $budgetAdditions->sum('amount');
        $totalExpenses = (float) $expenses->sum('amount');
        $currentBudget = $totalBudget - $totalExpenses - $reservedProcurement;
        $budgetUtilization = $totalBudget > 0
            ? round((($totalExpenses + $reservedProcurement) / $totalBudget) * 100, 1)
            : 0;

        return compact(
            'project',
            'transactions',
            'budgetAdditions',
            'expenses',
            'categorySummary',
            'reservedProcurement',
            'currentBudget',
            'totalBudget',
            'budgetUtilization'
        );
    }

    private function assertCanExport(): void
    {
        if (!Auth::check() || !Auth::user()->hasModuleAccess(\App\Models\User::MODULE_LEDGER)) {
            abort(403, 'You do not have permission to access this area.');
        }
    }

    private function resolveLogoPath(): ?string
    {
        foreach ([public_path('images/logo.jpg'), public_path('images/Logo.jpg')] as $logoPath) {
            if (file_exists($logoPath)) {
                return $logoPath;
            }
        }

        return null;
    }

    private function getLogoBase64(): string
    {
        $logoPath = $this->resolveLogoPath();

        if ($logoPath === null) {
            return '';
        }

        return 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
    }

    private function safeSpreadsheetText(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return preg_match(self::FORMULA_PREFIX_PATTERN, ltrim($value)) ? "'{$value}" : $value;
    }

    private function setSheetText($sheet, string $cell, ?string $value): void
    {
        $sheet->setCellValueExplicit($cell, $this->safeSpreadsheetText($value), DataType::TYPE_STRING);
    }

    private function formatMoney(mixed $amount): string
    {
        return 'PHP ' . number_format((float) $amount, 2);
    }

    public function exportExcel(Project $project): Response
    {
        return $this->downloadExcel($project);
    }

    public function exportPdf(Project $project): Response
    {
        return $this->downloadPdf($project);
    }

    public function exportWord(Project $project): Response
    {
        return $this->downloadWord($project);
    }

    public function downloadExcel(Project $project): Response
    {
        $this->assertCanExport();
        $data = $this->reportData($project);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Financial Report');

        $black = '000000';
        $darkGray = '333333';
        $mediumGray = '666666';
        $lightGray = 'F5F5F5';
        $white = 'FFFFFF';
        $border = 'CCCCCC';

        foreach (['A' => 14, 'B' => 35, 'C' => 14, 'D' => 18, 'E' => 22, 'F' => 16, 'G' => 18] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $centerAlign = ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER];
        $rightAlign = ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER];

        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'FINANCIAL STATEMENT REPORT');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($black));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:G2');
        $this->setSheetText($sheet, 'A2', $project->name);
        $sheet->getStyle('A2')->getFont()->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($mediumGray));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:G3');
        $sheet->setCellValue('A3', 'Report Date: ' . now()->format('F d, Y'));
        $sheet->getStyle('A3')->getFont()->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($mediumGray));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 5;
        $sheet->mergeCells("A{$row}:G{$row}");
        $sheet->setCellValue("A{$row}", 'EXECUTIVE SUMMARY');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => $white]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $darkGray]],
            'alignment' => $centerAlign,
        ]);
        $row++;

        $summaryItems = [
            ['Initial Budget', $this->formatMoney($project->budget)],
            ['Budget Additions', $this->formatMoney($data['budgetAdditions']->sum('amount'))],
            ['Total Budget', $this->formatMoney($data['totalBudget'])],
            ['Total Expenses', $this->formatMoney($data['expenses']->sum('amount'))],
            ['Current Balance', $this->formatMoney($data['currentBudget'])],
            ['Budget Utilization', $data['budgetUtilization'] . '%'],
        ];

        foreach ($summaryItems as $index => [$label, $value]) {
            $col = chr(65 + ($index % 3) * 2);
            $colEnd = chr(65 + ($index % 3) * 2 + 1);
            $baseRow = $row + (intdiv($index, 3) * 2);

            $sheet->mergeCells("{$col}{$baseRow}:{$colEnd}{$baseRow}");
            $this->setSheetText($sheet, "{$col}{$baseRow}", $label);
            $sheet->getStyle("{$col}{$baseRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => $mediumGray]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $lightGray]],
                'alignment' => $centerAlign,
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $border]]],
            ]);

            $sheet->mergeCells("{$col}" . ($baseRow + 1) . ":{$colEnd}" . ($baseRow + 1));
            $this->setSheetText($sheet, "{$col}" . ($baseRow + 1), $value);
            $sheet->getStyle("{$col}" . ($baseRow + 1))->applyFromArray([
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => $black]],
                'alignment' => $centerAlign,
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $border]]],
            ]);
        }

        $row += 5;
        $sheet->mergeCells("A{$row}:G{$row}");
        $sheet->setCellValue("A{$row}", 'TRANSACTION LEDGER');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => $white]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $darkGray]],
            'alignment' => $centerAlign,
        ]);
        $row++;

        $headers = ['DATE', 'DESCRIPTION', 'TYPE', 'CATEGORY', 'CLIENT/REFERENCE', 'AMOUNT (PHP)', 'RUNNING BALANCE (PHP)'];
        $column = 'A';
        foreach ($headers as $header) {
            $this->setSheetText($sheet, $column . $row, $header);
            $sheet->getStyle($column . $row)->applyFromArray([
                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => $white]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $darkGray]],
                'alignment' => in_array($header, ['AMOUNT (PHP)', 'RUNNING BALANCE (PHP)'], true) ? $rightAlign : $centerAlign,
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $border]]],
            ]);
            $column++;
        }
        $row++;

        $runningBalance = (float) $project->budget;
        foreach ($data['transactions'] as $index => $transaction) {
            $runningBalance += $transaction->type === 'budget_addition'
                ? (float) $transaction->amount
                : -(float) $transaction->amount;

            $bgColor = $index % 2 === 0 ? $white : $lightGray;
            $currentRow = $row + $index;

            $this->setSheetText($sheet, 'A' . $currentRow, $transaction->transaction_date?->format('Y-m-d'));
            $this->setSheetText($sheet, 'B' . $currentRow, $transaction->expense_name ?? $transaction->description ?? '-');
            $this->setSheetText($sheet, 'C' . $currentRow, $transaction->type === 'budget_addition' ? 'ADDITION' : 'EXPENSE');
            $this->setSheetText($sheet, 'D' . $currentRow, $transaction->category ?? '-');
            $this->setSheetText($sheet, 'E' . $currentRow, $transaction->client_name ?? $transaction->invoice_ref ?? '-');
            $sheet->setCellValue('F' . $currentRow, (float) $transaction->amount);
            $sheet->setCellValue('G' . $currentRow, $runningBalance);

            $sheet->getStyle('A' . $currentRow . ':G' . $currentRow)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $border]]],
            ]);
            $sheet->getStyle('F' . $currentRow . ':G' . $currentRow)->getNumberFormat()->setFormatCode('"PHP "#,##0.00');
            $sheet->getStyle('F' . $currentRow . ':G' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $row += $data['transactions']->count() + 2;
        $sheet->mergeCells("A{$row}:G{$row}");
        $sheet->setCellValue("A{$row}", 'EXPENSE BREAKDOWN BY CATEGORY');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => $white]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $darkGray]],
            'alignment' => $centerAlign,
        ]);
        $row++;

        $this->setSheetText($sheet, 'A' . $row, 'CATEGORY');
        $this->setSheetText($sheet, 'B' . $row, 'AMOUNT (PHP)');
        $this->setSheetText($sheet, 'C' . $row, 'PERCENTAGE');
        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => $white]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $darkGray]],
            'alignment' => $centerAlign,
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $border]]],
        ]);
        $row++;

        $expenseTotal = (float) $data['expenses']->sum('amount');
        foreach ($data['categorySummary'] as $category => $amount) {
            $percentage = $expenseTotal > 0 ? round(((float) $amount / $expenseTotal) * 100, 2) : 0;

            $this->setSheetText($sheet, 'A' . $row, $category);
            $sheet->setCellValue('B' . $row, (float) $amount);
            $this->setSheetText($sheet, 'C' . $row, $percentage . '%');

            $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $lightGray]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $border]]],
            ]);
            $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('"PHP "#,##0.00');
            $sheet->getStyle('B' . $row . ':C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $row++;
        }

        $this->setSheetText($sheet, 'A' . $row, 'TOTAL');
        $sheet->setCellValue('B' . $row, $expenseTotal);
        $this->setSheetText($sheet, 'C' . $row, '100%');
        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $lightGray]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $border]]],
        ]);
        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('"PHP "#,##0.00');
        $sheet->getStyle('B' . $row . ':C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $row += 2;
        $sheet->mergeCells("A{$row}:G{$row}");
        $sheet->setCellValue("A{$row}", 'This is a computer-generated document. No signature required.');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['italic' => true, 'size' => 8, 'color' => ['rgb' => $mediumGray]],
            'alignment' => $centerAlign,
        ]);

        $filename = 'Financial_Report_' . str($project->name)->slug() . '_' . date('Y-m-d') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = (string) ob_get_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function downloadPdf(Project $project): Response
    {
        $this->assertCanExport();

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', realpath(base_path()));

        $pdf = new Dompdf($options);
        $pdf->loadHtml($this->buildFormalReportHtml($this->reportData($project)));
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        $filename = 'Financial_Report_' . str($project->name)->slug() . '_' . date('Y-m-d') . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function downloadWord(Project $project): Response
    {
        $this->assertCanExport();
        $data = $this->reportData($project);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection([
            'marginTop' => 1000,
            'marginBottom' => 1000,
            'marginLeft' => 1200,
            'marginRight' => 1200,
        ]);

        $logoPath = $this->resolveLogoPath();
        if ($logoPath !== null) {
            $section->addImage($logoPath, ['width' => 80, 'height' => 80, 'alignment' => 'center']);
        }

        $section->addText('FINANCIAL STATEMENT REPORT', ['bold' => true, 'size' => 18], ['alignment' => 'center']);
        $section->addText($project->name, ['size' => 12, 'color' => '666666'], ['alignment' => 'center']);
        $section->addText('Report Date: ' . now()->format('F d, Y'), ['size' => 10, 'color' => '666666'], ['alignment' => 'center']);
        $section->addTextBreak();

        $section->addText('SUMMARY', ['bold' => true, 'size' => 12], ['alignment' => 'center']);
        $section->addTextBreak(0.5);

        $summaryTable = $section->addTable(['borderSize' => 1, 'borderColor' => 'CCCCCC', 'cellMargin' => 80]);
        $summaryItems = [
            ['Initial Budget', $this->formatMoney($project->budget)],
            ['Budget Additions', $this->formatMoney($data['budgetAdditions']->sum('amount'))],
            ['Total Budget', $this->formatMoney($data['totalBudget'])],
            ['Total Expenses', $this->formatMoney($data['expenses']->sum('amount'))],
            ['Current Balance', $this->formatMoney($data['currentBudget'])],
            ['Budget Utilization', $data['budgetUtilization'] . '%'],
        ];

        for ($rowIndex = 0; $rowIndex < 2; $rowIndex++) {
            $tableRow = $summaryTable->addRow();

            for ($columnIndex = 0; $columnIndex < 3; $columnIndex++) {
                $summaryIndex = ($rowIndex * 3) + $columnIndex;

                if (!isset($summaryItems[$summaryIndex])) {
                    continue;
                }

                [$label, $value] = $summaryItems[$summaryIndex];
                $cell = $tableRow->addCell(2500);
                $cell->addText($label, ['bold' => true, 'size' => 8, 'color' => '666666'], ['alignment' => 'center']);
                $cell->addText($value, ['bold' => true, 'size' => 12], ['alignment' => 'center']);
            }
        }

        $section->addTextBreak();
        $section->addText('TRANSACTION LEDGER', ['bold' => true, 'size' => 12], ['alignment' => 'center']);
        $section->addTextBreak(0.5);

        $ledgerTable = $section->addTable(['borderSize' => 1, 'borderColor' => 'CCCCCC', 'cellMargin' => 60]);
        $headerRow = $ledgerTable->addRow();
        foreach (['DATE', 'DESCRIPTION', 'TYPE', 'CATEGORY', 'CLIENT/REFERENCE', 'AMOUNT', 'BALANCE'] as $header) {
            $headerRow->addCell(null, ['bgColor' => '333333'])->addText($header, ['bold' => true, 'color' => 'FFFFFF', 'size' => 8], ['alignment' => 'center']);
        }

        $runningBalance = (float) $project->budget;
        foreach ($data['transactions'] as $index => $transaction) {
            $runningBalance += $transaction->type === 'budget_addition'
                ? (float) $transaction->amount
                : -(float) $transaction->amount;

            $bgColor = $index % 2 === 0 ? 'FFFFFF' : 'F5F5F5';
            $tableRow = $ledgerTable->addRow();
            $tableRow->addCell(null, ['bgColor' => $bgColor])->addText($transaction->transaction_date?->format('Y-m-d') ?? '-', ['size' => 8]);
            $tableRow->addCell(null, ['bgColor' => $bgColor])->addText($transaction->expense_name ?? $transaction->description ?? '-', ['size' => 8]);
            $tableRow->addCell(null, ['bgColor' => $bgColor])->addText($transaction->type === 'budget_addition' ? 'ADD' : 'EXP', ['size' => 8], ['alignment' => 'center']);
            $tableRow->addCell(null, ['bgColor' => $bgColor])->addText($transaction->category ?? '-', ['size' => 8]);
            $tableRow->addCell(null, ['bgColor' => $bgColor])->addText($transaction->client_name ?? $transaction->invoice_ref ?? '-', ['size' => 8]);
            $tableRow->addCell(null, ['bgColor' => $bgColor])->addText($this->formatMoney($transaction->amount), ['size' => 8], ['alignment' => 'right']);
            $tableRow->addCell(null, ['bgColor' => $bgColor])->addText($this->formatMoney($runningBalance), ['size' => 8], ['alignment' => 'right']);
        }

        $section->addTextBreak();
        $section->addText('EXPENSE BREAKDOWN BY CATEGORY', ['bold' => true, 'size' => 12], ['alignment' => 'center']);
        $section->addTextBreak(0.5);

        $categoryTable = $section->addTable(['borderSize' => 1, 'borderColor' => 'CCCCCC']);
        $categoryHeader = $categoryTable->addRow();
        $categoryHeader->addCell(null, ['bgColor' => '333333'])->addText('CATEGORY', ['bold' => true, 'color' => 'FFFFFF']);
        $categoryHeader->addCell(null, ['bgColor' => '333333'])->addText('AMOUNT', ['bold' => true, 'color' => 'FFFFFF'], ['alignment' => 'right']);
        $categoryHeader->addCell(null, ['bgColor' => '333333'])->addText('PERCENTAGE', ['bold' => true, 'color' => 'FFFFFF'], ['alignment' => 'right']);

        $expenseTotal = (float) $data['expenses']->sum('amount');
        foreach ($data['categorySummary'] as $category => $amount) {
            $percentage = $expenseTotal > 0 ? round(((float) $amount / $expenseTotal) * 100, 2) : 0;
            $tableRow = $categoryTable->addRow();
            $tableRow->addCell(null, ['bgColor' => 'F5F5F5'])->addText($category);
            $tableRow->addCell(null, ['bgColor' => 'F5F5F5'])->addText($this->formatMoney($amount), [], ['alignment' => 'right']);
            $tableRow->addCell(null, ['bgColor' => 'F5F5F5'])->addText($percentage . '%', [], ['alignment' => 'right']);
        }

        $totalRow = $categoryTable->addRow();
        $totalRow->addCell(null, ['bgColor' => 'F0F0F0'])->addText('TOTAL', ['bold' => true]);
        $totalRow->addCell(null, ['bgColor' => 'F0F0F0'])->addText($this->formatMoney($expenseTotal), ['bold' => true], ['alignment' => 'right']);
        $totalRow->addCell(null, ['bgColor' => 'F0F0F0'])->addText('100%', ['bold' => true], ['alignment' => 'right']);

        $section->addTextBreak();
        $section->addText('This is a computer-generated document. No signature required.', ['italic' => true, 'size' => 8, 'color' => '999999'], ['alignment' => 'center']);

        $filename = 'Financial_Report_' . str($project->name)->slug() . '_' . date('Y-m-d') . '.docx';
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        ob_start();
        $writer->save('php://output');
        $content = (string) ob_get_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function buildFormalReportHtml(array $data): string
    {
        $project = $data['project'];
        $transactions = $data['transactions'];
        $categorySummary = $data['categorySummary'];
        $expenseTotal = (float) $data['expenses']->sum('amount');
        $logoBase64 = $this->getLogoBase64();

        $runningBalance = (float) $project->budget;
        $transactionRows = '';
        foreach ($transactions as $transaction) {
            $runningBalance += $transaction->type === 'budget_addition'
                ? (float) $transaction->amount
                : -(float) $transaction->amount;

            $transactionRows .= '
            <tr>
                <td>' . e($transaction->transaction_date?->format('Y-m-d') ?? '-') . '</td>
                <td>' . e($transaction->expense_name ?? $transaction->description ?? '-') . '</td>
                <td>' . e($transaction->type === 'budget_addition' ? 'ADDITION' : 'EXPENSE') . '</td>
                <td>' . e($transaction->category ?? '-') . '</td>
                <td>' . e($transaction->client_name ?? $transaction->invoice_ref ?? '-') . '</td>
                <td class="number">' . e($this->formatMoney($transaction->amount)) . '</td>
                <td class="number">' . e($this->formatMoney($runningBalance)) . '</td>
            </tr>';
        }

        $categoryRows = '';
        foreach ($categorySummary as $category => $amount) {
            $percentage = $expenseTotal > 0 ? round(((float) $amount / $expenseTotal) * 100, 2) : 0;

            $categoryRows .= '
            <tr>
                <td>' . e($category) . '</td>
                <td class="number">' . e($this->formatMoney($amount)) . '</td>
                <td class="number">' . e($percentage . '%') . '</td>
            </tr>';
        }

        $logoHtml = $logoBase64 !== ''
            ? '<div class="logo"><img src="' . $logoBase64 . '" alt="Logo"></div>'
            : '';

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Financial Report - ' . e($project->name) . '</title>
            <style>
                @page { margin: 1.5cm; size: A4; }
                body {
                    font-family: DejaVu Sans, Arial, sans-serif;
                    font-size: 10pt;
                    line-height: 1.4;
                    color: #111827;
                    margin: 0;
                }
                .header {
                    text-align: center;
                    margin-bottom: 24px;
                    padding-bottom: 12px;
                    border-bottom: 2px solid #111827;
                }
                .logo { margin-bottom: 8px; }
                .logo img { max-width: 60px; max-height: 60px; }
                .company-name { font-size: 16pt; font-weight: bold; margin-bottom: 4px; }
                .project-name { font-size: 12pt; font-weight: bold; margin-bottom: 4px; }
                .report-date { font-size: 9pt; color: #6b7280; }
                .summary-title, .section-title {
                    font-size: 11pt;
                    font-weight: bold;
                    margin: 18px 0 10px;
                    padding-bottom: 4px;
                    border-bottom: 1px solid #111827;
                }
                table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
                th, td { border: 1px solid #d1d5db; padding: 6px; font-size: 9pt; }
                th { background: #333333; color: #ffffff; text-align: center; }
                .summary-table td { width: 25%; border: none; padding: 4px 6px; }
                .summary-label { font-weight: bold; color: #374151; }
                .number { text-align: right; }
                tr:nth-child(even) { background: #f9fafb; }
                .footer {
                    margin-top: 24px;
                    padding-top: 10px;
                    border-top: 1px solid #d1d5db;
                    text-align: center;
                    font-size: 8pt;
                    color: #6b7280;
                    font-style: italic;
                }
            </style>
        </head>
        <body>
            <div class="header">
                ' . $logoHtml . '
                <div class="company-name">FINANCIAL STATEMENT REPORT</div>
                <div class="project-name">' . e($project->name) . '</div>
                <div class="report-date">Generated: ' . e(now()->format('F d, Y')) . '</div>
            </div>

            <div class="summary-title">SUMMARY</div>
            <table class="summary-table">
                <tr>
                    <td class="summary-label">Initial Budget:</td>
                    <td>' . e($this->formatMoney($project->budget)) . '</td>
                    <td class="summary-label">Budget Additions:</td>
                    <td>' . e($this->formatMoney($data['budgetAdditions']->sum('amount'))) . '</td>
                </tr>
                <tr>
                    <td class="summary-label">Total Budget:</td>
                    <td>' . e($this->formatMoney($data['totalBudget'])) . '</td>
                    <td class="summary-label">Total Expenses:</td>
                    <td>' . e($this->formatMoney($data['expenses']->sum('amount'))) . '</td>
                </tr>
                <tr>
                    <td class="summary-label">Current Balance:</td>
                    <td>' . e($this->formatMoney($data['currentBudget'])) . '</td>
                    <td class="summary-label">Budget Utilization:</td>
                    <td>' . e($data['budgetUtilization'] . '%') . '</td>
                </tr>
            </table>

            <div class="section-title">TRANSACTION LEDGER</div>
            <table>
                <thead>
                    <tr>
                        <th>DATE</th>
                        <th>DESCRIPTION</th>
                        <th>TYPE</th>
                        <th>CATEGORY</th>
                        <th>CLIENT/REFERENCE</th>
                        <th>AMOUNT</th>
                        <th>BALANCE</th>
                    </tr>
                </thead>
                <tbody>' . $transactionRows . '</tbody>
            </table>

            <div class="section-title">EXPENSE BREAKDOWN BY CATEGORY</div>
            <table>
                <thead>
                    <tr>
                        <th>CATEGORY</th>
                        <th>AMOUNT</th>
                        <th>PERCENTAGE</th>
                    </tr>
                </thead>
                <tbody>
                    ' . $categoryRows . '
                    <tr style="font-weight: bold; background: #f3f4f6;">
                        <td>TOTAL</td>
                        <td class="number">' . e($this->formatMoney($expenseTotal)) . '</td>
                        <td class="number">100%</td>
                    </tr>
                </tbody>
            </table>

            <div class="footer">
                This is a computer-generated document. No signature required.
            </div>
        </body>
        </html>';
    }
}
