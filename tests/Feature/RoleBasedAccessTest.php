<?php

use App\Models\AccessRole;
use App\Models\Project;
use App\Models\InventoryItem;
use App\Models\MaterialRequest;
use App\Models\ProjectMonitoringPhoto;
use App\Models\ProjectMonitoringReport;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can access admin modules', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('projects.index'))
        ->assertOk();
});

test('employee cannot access admin modules', function () {
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($employee)
        ->get(route('projects.index'))
        ->assertForbidden();
});

test('office engineer can access operational modules but not admin-only modules', function () {
    $officeEngineer = User::factory()->create(['role' => 'office_engineer']);

    $this->actingAs($officeEngineer)->get(route('documents.index'))->assertOk();
    $this->actingAs($officeEngineer)->get(route('monitoring.index'))->assertOk();
    $this->actingAs($officeEngineer)->get(route('inventory.index'))->assertOk();
    $this->actingAs($officeEngineer)->get(route('material-requests.index'))->assertOk();

    $this->actingAs($officeEngineer)->get(route('projects.index'))->assertForbidden();
    $this->actingAs($officeEngineer)->get(route('settings.users.index'))->assertForbidden();
    $this->actingAs($officeEngineer)->get(route('monitoring.submit'))->assertForbidden();
});

test('employee can access monitoring submission', function () {
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($employee)
        ->get(route('monitoring.submit'))
        ->assertOk();
});

test('admin cannot access employee-only monitoring submission', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('monitoring.submit'))
        ->assertForbidden();
});

test('employee with multiple modules can open the module dashboard', function () {
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($employee)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Project Monitoring')
        ->assertSee('Material Requests');
});

test('office engineer access can be limited to selected modules', function () {
    $officeEngineer = User::factory()->create([
        'role' => 'office_engineer',
        'module_permissions' => [User::MODULE_DOCUMENTS],
    ]);

    $this->actingAs($officeEngineer)->get(route('documents.index'))->assertOk();
    $this->actingAs($officeEngineer)->get(route('inventory.index'))->assertForbidden();
    $this->actingAs($officeEngineer)->get(route('material-requests.index'))->assertForbidden();
});

test('employee landing page and access follow assigned employee modules', function () {
    $employee = User::factory()->create([
        'role' => 'employee',
        'module_permissions' => [User::MODULE_MATERIAL_REQUESTS],
    ]);

    $this->actingAs($employee)
        ->get(route('dashboard'))
        ->assertRedirect(route('material-requests.create'));

    $this->actingAs($employee)
        ->get(route('material-requests.create'))
        ->assertOk();

    $this->actingAs($employee)
        ->get(route('monitoring.submit'))
        ->assertForbidden();
});

test('admin routes can be limited by explicit module assignments', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'module_permissions' => [User::MODULE_SETTINGS_PROJECTS],
    ]);

    $this->actingAs($admin)->get(route('settings.projects.index'))->assertOk();
    $this->actingAs($admin)->get(route('projects.index'))->assertForbidden();
    $this->actingAs($admin)->get(route('settings.users.index'))->assertForbidden();
});

test('user management creates an account with only the selected dashboard modules', function () {
    $manager = User::factory()->create(['role' => 'admin']);
    $officeRole = AccessRole::where('workflow_type', AccessRole::WORKFLOW_REVIEWER)->firstOrFail();

    $this->actingAs($manager)
        ->post(route('settings.users.store'), [
            'name' => 'Document Officer',
            'email' => 'document.officer@example.test',
            'position' => 'Records Officer',
            'access_role_id' => $officeRole->id,
            'module_permissions' => [User::MODULE_DOCUMENTS],
            'password' => 'StrongPass!123',
            'password_confirmation' => 'StrongPass!123',
        ])
        ->assertRedirect(route('settings.users.index'));

    $createdUser = User::where('email', 'document.officer@example.test')->firstOrFail();

    expect($createdUser->resolvedModulePermissions())->toBe([User::MODULE_DOCUMENTS]);
    $this->actingAs($createdUser)->get(route('documents.index'))->assertOk();
    $this->actingAs($createdUser)->get(route('inventory.index'))->assertForbidden();
});

test('field staff can be assigned any dashboard module', function () {
    $manager = User::factory()->create(['role' => 'admin']);
    $siteRole = AccessRole::where('workflow_type', AccessRole::WORKFLOW_FIELD_STAFF)->firstOrFail();
    $project = Project::create([
        'name' => 'Flexible Access Project',
        'budget' => 1000,
        'status' => 'not_started',
    ]);

    $this->actingAs($manager)
        ->post(route('settings.users.store'), [
            'name' => 'Flexible Site Engineer',
            'email' => 'flexible.site.engineer@example.test',
            'position' => 'Site Staff',
            'access_role_id' => $siteRole->id,
            'module_permissions' => [
                User::MODULE_LEDGER,
                User::MODULE_DOCUMENTS,
                User::MODULE_SETTINGS_USERS,
            ],
            'project_ids' => [$project->id],
            'password' => 'StrongPass!123',
            'password_confirmation' => 'StrongPass!123',
        ])
        ->assertRedirect(route('settings.users.index'));

    $createdUser = User::where('email', 'flexible.site.engineer@example.test')->firstOrFail();
    expect($createdUser->resolvedModulePermissions())->toBe([
        User::MODULE_LEDGER,
        User::MODULE_DOCUMENTS,
        User::MODULE_SETTINGS_USERS,
    ]);

    $this->actingAs($createdUser)->get(route('projects.index'))->assertOk();
    $this->actingAs($createdUser)->get(route('documents.index'))->assertOk();
    $this->actingAs($createdUser)->get(route('settings.users.index'))->assertOk();
    $this->actingAs($createdUser)
        ->put(route('projects.update', $project), [
            'name' => 'Updated by Site Engineer',
            'budget' => 1000,
            'status' => 'not_started',
        ])
        ->assertRedirect(route('projects.show', $project));

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Updated by Site Engineer',
    ]);
});

test('user edit buttons render a valid JSON payload', function () {
    $manager = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create([
        'name' => "O'Brien Project Officer",
        'role' => 'office_engineer',
        'position' => 'Project Officer',
        'module_permissions' => [User::MODULE_DOCUMENTS],
    ]);

    $response = $this->actingAs($manager)->get(route('settings.users.index'))->assertOk();
    $html = $response->getContent();

    expect(preg_match_all('/data-edit-user="([^"]+)"/', $html, $matches))->toBeGreaterThanOrEqual(2);

    $payloads = collect($matches[1])->map(
        fn (string $payload) => json_decode(
            html_entity_decode($payload, ENT_QUOTES),
            true,
            flags: JSON_THROW_ON_ERROR
        )
    );
    $payload = $payloads->firstWhere('id', $user->id);

    expect($payload)
        ->toBeArray()
        ->toHaveKeys(['id', 'name', 'position', 'email', 'accessRoleId', 'modulePermissions'])
        ->and($payload['name'])->toBe("O'Brien Project Officer");
});

test('administrator can create a custom planning team role with module defaults', function () {
    $manager = User::factory()->create(['role' => 'admin']);

    $this->actingAs($manager)
        ->post(route('settings.roles.store'), [
            'name' => 'Planning Team',
            'description' => 'Reviews plans, documents, and project progress.',
            'workflow_type' => AccessRole::WORKFLOW_REVIEWER,
            'module_permissions' => [
                User::MODULE_LEDGER,
                User::MODULE_DOCUMENTS,
                User::MODULE_MONITORING_REVIEW,
            ],
        ])
        ->assertRedirect(route('settings.roles.index'));

    $role = AccessRole::where('name', 'Planning Team')->firstOrFail();

    expect($role->workflow_type)->toBe(AccessRole::WORKFLOW_REVIEWER)
        ->and($role->module_permissions)->toBe([
            User::MODULE_LEDGER,
            User::MODULE_DOCUMENTS,
            User::MODULE_MONITORING_REVIEW,
        ]);
});

test('changing a custom role workflow remaps user workflow modules', function () {
    $manager = User::factory()->create(['role' => 'admin']);
    $role = AccessRole::create([
        'name' => 'Project Review Team',
        'slug' => 'project-review-team',
        'workflow_type' => AccessRole::WORKFLOW_REVIEWER,
        'module_permissions' => [
            User::MODULE_MONITORING_REVIEW,
            User::MODULE_MATERIAL_APPROVALS,
        ],
    ]);
    $member = User::factory()->create([
        'role' => 'office_engineer',
        'access_role_id' => $role->id,
        'module_permissions' => [
            User::MODULE_MONITORING_REVIEW,
            User::MODULE_MATERIAL_APPROVALS,
        ],
    ]);

    $this->actingAs($manager)
        ->put(route('settings.roles.update', $role), [
            'name' => 'Project Field Team',
            'workflow_type' => AccessRole::WORKFLOW_FIELD_STAFF,
            'module_permissions' => [
                User::MODULE_MONITORING_SUBMIT,
                User::MODULE_MATERIAL_REQUESTS,
            ],
        ])
        ->assertRedirect(route('settings.roles.index'));

    expect($member->fresh()->role)->toBe('employee')
        ->and($member->fresh()->module_permissions)->toBe([
            User::MODULE_MONITORING_SUBMIT,
            User::MODULE_MATERIAL_REQUESTS,
        ]);
});

test('monitoring photos are limited to admins and the report owner', function () {
    $owner = User::factory()->create(['role' => 'employee']);
    $otherEmployee = User::factory()->create(['role' => 'employee']);
    $project = Project::create([
        'name' => 'Test Project',
        'budget' => 1000,
        'status' => 'not_started',
    ]);
    $report = ProjectMonitoringReport::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'accomplishment_details' => 'Initial work',
        'estimated_completion_percentage' => 10,
        'status' => ProjectMonitoringReport::STATUS_PENDING,
    ]);
    $photo = ProjectMonitoringPhoto::create([
        'project_monitoring_report_id' => $report->id,
        'path' => 'monitoring_photos/missing.jpg',
    ]);

    $this->actingAs($otherEmployee)
        ->get(route('monitoring.photos.show', [$report, $photo->id]))
        ->assertForbidden();
});

test('employees can create material requests only for assigned projects', function () {
    $employee = User::factory()->create(['role' => 'employee']);
    $assignedProject = Project::create(['name' => 'Assigned Project', 'budget' => 1000, 'status' => 'not_started']);
    $otherProject = Project::create(['name' => 'Other Project', 'budget' => 1000, 'status' => 'not_started']);
    $employee->assignedProjects()->sync([$assignedProject->id]);

    $this->actingAs($employee)
        ->post(route('material-requests.store'), [
            'project_id' => $assignedProject->id,
            'material_name' => 'Rebar',
            'material_category' => 'Construction Materials',
            'quantity' => 12,
            'unit' => 'pcs',
            'purpose' => 'Foundation work',
            'request_note' => 'Needed before concrete pouring.',
            'date_requested' => now()->toDateString(),
        ])
        ->assertRedirect(route('material-requests.create'));

    $this->assertDatabaseHas('material_requests', [
        'project_id' => $assignedProject->id,
        'user_id' => $employee->id,
        'material_name' => 'Rebar',
        'material_category' => 'Construction Materials',
        'request_note' => 'Needed before concrete pouring.',
        'status' => MaterialRequest::STATUS_PENDING,
    ]);

    $this->actingAs($employee)
        ->post(route('material-requests.store'), [
            'project_id' => $otherProject->id,
            'material_name' => 'Cement',
            'quantity' => 1,
            'unit' => 'bag',
            'date_requested' => now()->toDateString(),
        ])
        ->assertSessionHasErrors('project_id');
});

test('office engineer approval deducts inventory and records audit', function () {
    $employee = User::factory()->create(['role' => 'employee']);
    $officeEngineer = User::factory()->create(['role' => 'office_engineer']);
    $project = Project::create(['name' => 'Inventory Project', 'budget' => 1000, 'status' => 'not_started']);
    $item = InventoryItem::create([
        'name' => 'Cement',
        'unit' => 'bags',
        'unit_cost' => 250,
        'quantity' => 10,
    ]);
    $materialRequest = MaterialRequest::create([
        'request_number' => 'MR-TEST-0001',
        'project_id' => $project->id,
        'user_id' => $employee->id,
        'inventory_item_id' => $item->id,
        'material_name' => $item->name,
        'quantity' => 4,
        'unit' => $item->unit,
        'date_requested' => now()->toDateString(),
        'status' => MaterialRequest::STATUS_PENDING,
    ]);

    $this->actingAs($officeEngineer)
        ->post(route('material-requests.approve', $materialRequest), [
            'decision' => 'issue',
        ])
        ->assertRedirect(route('material-requests.index'));

    $this->assertDatabaseHas('inventory_items', [
        'id' => $item->id,
        'quantity' => 6,
    ]);
    $this->assertDatabaseHas('material_requests', [
        'id' => $materialRequest->id,
        'status' => MaterialRequest::STATUS_ISSUED,
        'approved_quantity' => 4,
        'reviewed_by' => $officeEngineer->id,
        'actual_total_cost' => 1000,
        'budget_commitment_status' => MaterialRequest::COMMITMENT_CONVERTED,
    ]);
    $this->assertDatabaseHas('material_request_audits', [
        'material_request_id' => $materialRequest->id,
        'action' => 'inventory_deducted',
        'quantity' => 4,
    ]);
});

test('unlinked material requests cannot be issued before inventory is selected', function () {
    $employee = User::factory()->create(['role' => 'employee']);
    $officeEngineer = User::factory()->create(['role' => 'office_engineer']);
    $project = Project::create(['name' => 'Procurement Project', 'budget' => 1000, 'status' => 'not_started']);
    $materialRequest = MaterialRequest::create([
        'request_number' => 'MR-TEST-0002',
        'project_id' => $project->id,
        'user_id' => $employee->id,
        'material_name' => 'New Material',
        'quantity' => 2,
        'unit' => 'pcs',
        'date_requested' => now()->toDateString(),
        'status' => MaterialRequest::STATUS_PENDING,
    ]);

    $this->actingAs($officeEngineer)
        ->from(route('material-requests.index'))
        ->post(route('material-requests.approve', $materialRequest), [
            'decision' => 'issue',
            'approved_quantity' => 2,
        ])
        ->assertRedirect(route('material-requests.index'))
        ->assertSessionHasErrors('inventory_item_id');

    $this->assertDatabaseHas('material_requests', [
        'id' => $materialRequest->id,
        'status' => MaterialRequest::STATUS_PENDING,
        'inventory_item_id' => null,
    ]);
});

test('procurement reservation becomes one actual project expense when issued', function () {
    $employee = User::factory()->create(['role' => 'employee']);
    $officeEngineer = User::factory()->create(['role' => 'office_engineer']);
    $project = Project::create(['name' => 'Reserved Budget Project', 'budget' => 10000, 'status' => 'not_started']);
    $materialRequest = MaterialRequest::create([
        'request_number' => 'MR-TEST-0003',
        'project_id' => $project->id,
        'user_id' => $employee->id,
        'material_name' => 'Special Fastener',
        'quantity' => 4,
        'unit' => 'pcs',
        'date_requested' => now()->toDateString(),
        'status' => MaterialRequest::STATUS_PENDING,
    ]);

    $this->actingAs($officeEngineer)
        ->post(route('material-requests.approve', $materialRequest), [
            'decision' => 'waiting',
            'estimated_unit_cost' => 250,
            'procurement_note' => 'Awaiting supplier purchase.',
        ])
        ->assertRedirect(route('material-requests.index'));

    $this->assertDatabaseHas('material_requests', [
        'id' => $materialRequest->id,
        'status' => MaterialRequest::STATUS_WAITING_PROCUREMENT,
        'estimated_unit_cost' => 250,
        'estimated_total_cost' => 1000,
        'budget_commitment_status' => MaterialRequest::COMMITMENT_RESERVED,
    ]);
    expect(Transaction::where('project_id', $project->id)->count())->toBe(0);
    expect($project->fresh()->reserved_procurement)->toBe(1000.0);
    expect($project->fresh()->current_budget)->toBe(9000.0);

    $this->actingAs($officeEngineer)
        ->post(route('material-requests.approve', $materialRequest->fresh()), [
            'decision' => 'procure_issue',
            'estimated_unit_cost' => 300,
            'inventory_name' => 'Special Fastener',
            'inventory_category' => 'Hardware',
            'inventory_unit' => 'pcs',
            'inventory_description' => 'Project-specific fastener.',
            'procurement_note' => 'Purchased for immediate project use.',
        ])
        ->assertRedirect(route('material-requests.index'));

    $item = InventoryItem::where('name', 'Special Fastener')->firstOrFail();

    $this->assertDatabaseHas('inventory_items', [
        'id' => $item->id,
        'category' => 'Hardware',
        'unit_cost' => 300,
        'quantity' => 0,
    ]);
    $this->assertDatabaseHas('material_requests', [
        'id' => $materialRequest->id,
        'inventory_item_id' => $item->id,
        'status' => MaterialRequest::STATUS_ISSUED,
        'actual_total_cost' => 1200,
        'procurement_note' => 'Purchased for immediate project use.',
        'budget_commitment_status' => MaterialRequest::COMMITMENT_CONVERTED,
    ]);
    $this->assertDatabaseHas('inventory_assignments', [
        'inventory_item_id' => $item->id,
        'project_id' => $project->id,
        'quantity_assigned' => 4,
        'total_cost' => 1200,
    ]);
    expect(Transaction::where('project_id', $project->id)->where('type', 'expense')->count())->toBe(1);
    expect($project->fresh()->reserved_procurement)->toBe(0.0);
    expect($project->fresh()->current_budget)->toBe(8800.0);
});

test('rejecting a procurement request releases its project budget reservation', function () {
    $employee = User::factory()->create(['role' => 'employee']);
    $officeEngineer = User::factory()->create(['role' => 'office_engineer']);
    $project = Project::create(['name' => 'Released Budget Project', 'budget' => 5000, 'status' => 'not_started']);
    $materialRequest = MaterialRequest::create([
        'request_number' => 'MR-TEST-0004',
        'project_id' => $project->id,
        'user_id' => $employee->id,
        'material_name' => 'Custom Bracket',
        'quantity' => 5,
        'unit' => 'pcs',
        'date_requested' => now()->toDateString(),
        'status' => MaterialRequest::STATUS_WAITING_PROCUREMENT,
        'estimated_unit_cost' => 200,
        'estimated_total_cost' => 1000,
        'budget_commitment_status' => MaterialRequest::COMMITMENT_RESERVED,
    ]);

    expect($project->fresh()->current_budget)->toBe(4000.0);

    $this->actingAs($officeEngineer)
        ->post(route('material-requests.reject', $materialRequest), [
            'rejection_reason' => 'The requested material is no longer needed.',
        ])
        ->assertRedirect(route('material-requests.index'));

    $this->assertDatabaseHas('material_requests', [
        'id' => $materialRequest->id,
        'status' => MaterialRequest::STATUS_REJECTED,
        'budget_commitment_status' => MaterialRequest::COMMITMENT_RELEASED,
    ]);
    expect($project->fresh()->reserved_procurement)->toBe(0.0);
    expect($project->fresh()->current_budget)->toBe(5000.0);
});

test('procured out of stock item is restocked and issued without creating a duplicate', function () {
    $employee = User::factory()->create(['role' => 'employee']);
    $officeEngineer = User::factory()->create(['role' => 'office_engineer']);
    $project = Project::create(['name' => 'Restock Project', 'budget' => 5000, 'status' => 'not_started']);
    $item = InventoryItem::create([
        'name' => 'Steel Table',
        'category' => 'Materials',
        'unit' => 'pcs',
        'unit_cost' => 100,
        'quantity' => 0,
        'description' => 'Existing out-of-stock item.',
    ]);
    $materialRequest = MaterialRequest::create([
        'request_number' => 'MR-TEST-0005',
        'project_id' => $project->id,
        'user_id' => $employee->id,
        'inventory_item_id' => $item->id,
        'material_name' => $item->name,
        'material_category' => $item->category,
        'quantity' => 3,
        'unit' => $item->unit,
        'date_requested' => now()->toDateString(),
        'status' => MaterialRequest::STATUS_PENDING,
    ]);

    $this->actingAs($officeEngineer)
        ->post(route('material-requests.approve', $materialRequest), [
            'decision' => 'procure_issue',
            'estimated_unit_cost' => 150,
            'inventory_name' => $item->name,
            'inventory_category' => $item->category,
            'inventory_unit' => $item->unit,
            'inventory_description' => $item->description,
            'procurement_note' => 'Restocked for this project request.',
        ])
        ->assertRedirect(route('material-requests.index'));

    expect(InventoryItem::count())->toBe(1);
    $this->assertDatabaseHas('inventory_items', [
        'id' => $item->id,
        'unit_cost' => 150,
        'quantity' => 0,
    ]);
    $this->assertDatabaseHas('inventory_assignments', [
        'inventory_item_id' => $item->id,
        'project_id' => $project->id,
        'quantity_assigned' => 3,
        'total_cost' => 450,
    ]);
    $this->assertDatabaseHas('material_requests', [
        'id' => $materialRequest->id,
        'inventory_item_id' => $item->id,
        'status' => MaterialRequest::STATUS_ISSUED,
        'actual_total_cost' => 450,
    ]);
    $this->assertDatabaseHas('material_request_audits', [
        'material_request_id' => $materialRequest->id,
        'action' => 'inventory_restocked',
        'quantity' => 3,
    ]);
    expect($project->fresh()->current_budget)->toBe(4550.0);
});

test('inventory can be assigned without changing project budget or expenses', function () {
    $officeEngineer = User::factory()->create(['role' => 'office_engineer']);
    $project = Project::create(['name' => 'Stock Transfer Project', 'budget' => 1000, 'status' => 'not_started']);
    $item = InventoryItem::create([
        'name' => 'Existing Office Table',
        'unit' => 'pcs',
        'unit_cost' => 100,
        'quantity' => 10,
    ]);

    $this->actingAs($officeEngineer)
        ->post(route('inventory.doAssign', $item), [
            'project_id' => $project->id,
            'quantity_assigned' => 2,
            'charge_to_project' => 0,
            'notes' => 'Existing company-owned item.',
        ])
        ->assertRedirect(route('inventory.index'));

    $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'quantity' => 8]);
    $this->assertDatabaseHas('inventory_assignments', [
        'inventory_item_id' => $item->id,
        'project_id' => $project->id,
        'transaction_id' => null,
        'is_expense' => false,
        'quantity_assigned' => 2,
        'unit_cost_at_assignment' => 0,
        'total_cost' => 0,
    ]);
    expect(Transaction::where('project_id', $project->id)->count())->toBe(0);
    expect($project->fresh()->current_budget)->toBe(1000.0);
});

test('inventory expense assignment reduces the selected project budget', function () {
    $officeEngineer = User::factory()->create(['role' => 'office_engineer']);
    $project = Project::create(['name' => 'Charged Inventory Project', 'budget' => 1000, 'status' => 'not_started']);
    $item = InventoryItem::create([
        'name' => 'Project Materials',
        'unit' => 'pcs',
        'unit_cost' => 100,
        'quantity' => 10,
    ]);

    $this->actingAs($officeEngineer)
        ->post(route('inventory.doAssign', $item), [
            'project_id' => $project->id,
            'quantity_assigned' => 2,
            'charge_to_project' => 1,
        ])
        ->assertRedirect(route('inventory.index'));

    $this->assertDatabaseHas('inventory_assignments', [
        'inventory_item_id' => $item->id,
        'project_id' => $project->id,
        'is_expense' => true,
        'quantity_assigned' => 2,
        'unit_cost_at_assignment' => 100,
        'total_cost' => 200,
    ]);
    $this->assertDatabaseHas('transactions', [
        'project_id' => $project->id,
        'type' => 'expense',
        'amount' => 200,
    ]);
    expect($project->fresh()->current_budget)->toBe(800.0);
});
