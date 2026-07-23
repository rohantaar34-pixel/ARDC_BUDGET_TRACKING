<?php

use App\Models\Project;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;

uses(RefreshDatabase::class);

function makeProjectWithTransaction(array $transactionOverrides = []): Project
{
    $project = Project::create([
        'name' => 'Export Test Project',
        'budget' => 50000,
        'status' => 'not_started',
    ]);

    Transaction::create(array_merge([
        'project_id' => $project->id,
        'type' => 'expense',
        'expense_name' => 'Concrete Purchase',
        'category' => 'Materials',
        'amount' => 1250.50,
        'description' => 'Initial export check',
        'transaction_date' => '2026-07-01',
        'client_name' => 'ARDC',
    ], $transactionOverrides));

    return $project;
}

test('admin can export project ledger to excel pdf and word', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $project = makeProjectWithTransaction();

    $this->actingAs($admin)
        ->get(route('projects.report.excel', $project))
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $this->actingAs($admin)
        ->get(route('projects.report.pdf', $project))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    $this->actingAs($admin)
        ->get(route('projects.report.word', $project))
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
});

test('non admins cannot export project ledger reports', function () {
    $employee = User::factory()->create(['role' => 'employee']);
    $project = makeProjectWithTransaction();

    $this->actingAs($employee)
        ->get(route('projects.report.excel', $project))
        ->assertForbidden();

    $this->actingAs($employee)
        ->get(route('projects.report.pdf', $project))
        ->assertForbidden();

    $this->actingAs($employee)
        ->get(route('projects.report.word', $project))
        ->assertForbidden();
});

test('excel export neutralizes formula-like user content', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $project = makeProjectWithTransaction([
        'expense_name' => '=HYPERLINK("http://evil.test","click")',
        'category' => '+SUM(1,1)',
        'client_name' => '@cmd',
    ]);

    $response = $this->actingAs($admin)->get(route('projects.report.excel', $project));
    $response->assertOk();

    $tempPath = tempnam(sys_get_temp_dir(), 'ledger-report-');
    $xlsxPath = $tempPath . '.xlsx';
    file_put_contents($xlsxPath, $response->getContent());

    try {
        $spreadsheet = IOFactory::load($xlsxPath);
        $sheet = $spreadsheet->getActiveSheet();

        expect($sheet->getCell('B13')->getDataType())->toBe(DataType::TYPE_STRING);
        expect($sheet->getCell('B13')->getValue())->toBe('\'=HYPERLINK("http://evil.test","click")');
        expect($sheet->getCell('D13')->getDataType())->toBe(DataType::TYPE_STRING);
        expect($sheet->getCell('D13')->getValue())->toBe('\'+SUM(1,1)');
        expect($sheet->getCell('E13')->getDataType())->toBe(DataType::TYPE_STRING);
        expect($sheet->getCell('E13')->getValue())->toBe('\'@cmd');
    } finally {
        if (file_exists($xlsxPath)) {
            unlink($xlsxPath);
        }

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }
});
