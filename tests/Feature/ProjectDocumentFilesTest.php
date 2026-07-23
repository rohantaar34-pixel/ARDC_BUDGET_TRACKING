<?php

use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function projectFileDocument(Project $project, User $uploader, array $overrides = []): Document
{
    return Document::create(array_merge([
        'document_number' => 'DOC-'.$project->id.'-'.fake()->unique()->numerify('####'),
        'title' => 'Project File',
        'description' => 'Detailed project document',
        'document_type' => 'report',
        'category' => 'technical',
        'project_id' => $project->id,
        'uploaded_by' => $uploader->id,
        'status' => 'active',
        'version' => 1,
        'date_added' => now(),
    ], $overrides));
}

test('document users can open a project folder and see complete file metadata', function () {
    $documentUser = User::factory()->create([
        'role' => 'office_engineer',
        'module_permissions' => [User::MODULE_DOCUMENTS],
        'name' => 'Records Engineer',
    ]);
    $project = Project::create([
        'name' => 'Municipal Hall Project',
        'description' => 'Main project folder',
        'budget' => 200000,
        'status' => 'in_progress',
    ]);
    $otherProject = Project::create([
        'name' => 'Other Project',
        'budget' => 100000,
        'status' => 'not_started',
    ]);

    projectFileDocument($project, $documentUser, [
        'title' => 'Structural Inspection Report',
        'original_filename' => 'inspection-report.pdf',
        'file_path' => 'documents/files/inspection-report.pdf',
        'file_size' => 2048,
        'file_extension' => 'pdf',
        'mime_type' => 'application/pdf',
        'expiry_date' => today()->addDays(10),
    ]);
    projectFileDocument($otherProject, $documentUser, [
        'title' => 'Confidential Other Project File',
    ]);

    $this->actingAs($documentUser)
        ->get(route('documents.project', $project))
        ->assertOk()
        ->assertSee('Municipal Hall Project')
        ->assertSee('Structural Inspection Report')
        ->assertSee('inspection-report.pdf')
        ->assertSee('Records Engineer')
        ->assertSee('In 10 days')
        ->assertDontSee('Confidential Other Project File');
});

test('project folder shows all project files without extra filtering steps', function () {
    $documentUser = User::factory()->create([
        'role' => 'office_engineer',
        'module_permissions' => [User::MODULE_DOCUMENTS],
    ]);
    $project = Project::create([
        'name' => 'Filtered Project',
        'budget' => 50000,
        'status' => 'not_started',
    ]);
    projectFileDocument($project, $documentUser, [
        'title' => 'Active Contract',
        'document_type' => 'contract',
    ]);
    projectFileDocument($project, $documentUser, [
        'title' => 'Archived Report',
        'document_type' => 'report',
        'status' => 'archived',
    ]);

    $this->actingAs($documentUser)
        ->get(route('documents.project', $project))
        ->assertOk()
        ->assertSee('Active Contract')
        ->assertSee('Archived Report')
        ->assertDontSee('Apply filters');
});

test('project folders and scanned files require document module access', function () {
    Storage::fake('public');
    $documentUser = User::factory()->create([
        'role' => 'office_engineer',
        'module_permissions' => [User::MODULE_DOCUMENTS],
    ]);
    $fieldStaff = User::factory()->create([
        'role' => 'employee',
        'module_permissions' => [User::MODULE_MONITORING_SUBMIT],
    ]);
    $project = Project::create([
        'name' => 'Protected Project',
        'budget' => 50000,
        'status' => 'not_started',
    ]);
    Storage::disk('public')->put('documents/scans/protected.jpg', 'image-data');
    $document = projectFileDocument($project, $documentUser, [
        'scanned_image_path' => 'documents/scans/protected.jpg',
    ]);

    $this->actingAs($documentUser)
        ->get(route('documents.scan', $document))
        ->assertOk();

    $this->actingAs($fieldStaff)
        ->get(route('documents.project', $project))
        ->assertForbidden();

    $this->actingAs($fieldStaff)
        ->get(route('documents.scan', $document))
        ->assertForbidden();
});

test('every project card links to its detailed document folder', function () {
    $documentUser = User::factory()->create([
        'role' => 'office_engineer',
        'module_permissions' => [User::MODULE_DOCUMENTS],
    ]);
    $project = Project::create([
        'name' => 'Linked Folder Project',
        'budget' => 50000,
        'status' => 'not_started',
    ]);

    $this->actingAs($documentUser)
        ->get(route('documents.index'))
        ->assertOk()
        ->assertSee(route('documents.project', $project), false)
        ->assertSee('Open folder and view all details');
});
