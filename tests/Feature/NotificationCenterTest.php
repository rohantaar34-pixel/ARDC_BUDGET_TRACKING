<?php

use App\Models\Document;
use App\Models\User;
use App\Notifications\InAppNotification;
use App\Services\DocumentExpiryNotifier;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function notificationUser(string $role, ?array $modules = null): User
{
    return User::factory()->create([
        'role' => $role,
        'module_permissions' => $modules,
    ]);
}

test('project creation broadcasts a notification to every department', function () {
    $admin = notificationUser('admin');
    $reviewer = notificationUser('office_engineer');
    $fieldStaff = notificationUser('employee');

    $this->actingAs($admin)
        ->post(route('settings.projects.store'), [
            'name' => 'North Wing Renovation',
            'description' => 'Notification integration project',
            'budget' => 150000,
            'status' => 'not_started',
        ])
        ->assertRedirect(route('settings.projects.index'));

    foreach ([$admin, $reviewer, $fieldStaff] as $recipient) {
        expect($recipient->fresh()->notifications)
            ->toHaveCount(1)
            ->and($recipient->fresh()->notifications->first()->data['kind'])->toBe('project');
    }
});

test('document upload and expiry notices only reach the document department', function () {
    $uploader = notificationUser('admin');
    $documentUser = notificationUser('office_engineer', [User::MODULE_DOCUMENTS]);
    $fieldStaff = notificationUser('employee');

    $this->actingAs($uploader)
        ->post(route('documents.store'), [
            'document_number' => 'DOC-NOTIFY-001',
            'title' => 'Insurance Certificate',
            'document_type' => 'contract',
            'document_date' => '2026-07-20',
            'expiry_date' => '2026-08-01',
        ])
        ->assertRedirect(route('documents.index'));

    expect($uploader->fresh()->notifications)->toHaveCount(2)
        ->and($documentUser->fresh()->notifications)->toHaveCount(2)
        ->and($fieldStaff->fresh()->notifications)->toHaveCount(0);

    $kinds = $documentUser->fresh()->notifications->pluck('data')
        ->pluck('kind')
        ->sort()
        ->values()
        ->all();

    expect($kinds)->toBe(['document', 'expiry']);
});

test('document expiry command does not duplicate the same expiry notice', function () {
    $documentUser = notificationUser('office_engineer', [User::MODULE_DOCUMENTS]);
    Document::create([
        'document_number' => 'DOC-EXPIRY-001',
        'title' => 'Safety Permit',
        'document_type' => 'permit',
        'expiry_date' => '2026-08-02',
        'status' => 'active',
        'date_added' => '2026-07-01 08:00:00',
    ]);

    $notifier = app(DocumentExpiryNotifier::class);
    $today = CarbonImmutable::parse('2026-07-23');
    $firstRun = $notifier->sendUpcoming($today);
    $secondRun = $notifier->sendUpcoming($today);

    expect($firstRun)->toBeGreaterThan(0)
        ->and($secondRun)->toBe(0)
        ->and($documentUser->fresh()->notifications)->toHaveCount(1)
        ->and($documentUser->fresh()->notifications->first()->data['kind'])->toBe('expiry');
});

test('administrator can broadcast to all users or selected module departments', function () {
    $admin = notificationUser('admin');
    $documentUser = notificationUser('office_engineer', [User::MODULE_DOCUMENTS]);
    $fieldStaff = notificationUser('employee');

    $this->actingAs($admin)
        ->post(route('notifications.broadcast'), [
            'title' => 'Document audit',
            'message' => 'Please review the latest uploaded records.',
            'audience' => 'modules',
            'modules' => [User::MODULE_DOCUMENTS],
        ])
        ->assertRedirect(route('notifications.index'));

    expect($admin->fresh()->notifications)->toHaveCount(1)
        ->and($documentUser->fresh()->notifications)->toHaveCount(1)
        ->and($fieldStaff->fresh()->notifications)->toHaveCount(0);

    $this->actingAs($admin)
        ->post(route('notifications.broadcast'), [
            'title' => 'Office closure',
            'message' => 'All departments should read this announcement.',
            'audience' => 'all',
        ])
        ->assertRedirect(route('notifications.index'));

    expect($admin->fresh()->notifications)->toHaveCount(2)
        ->and($documentUser->fresh()->notifications)->toHaveCount(2)
        ->and($fieldStaff->fresh()->notifications)->toHaveCount(1);
});

test('ordinary users cannot broadcast and cannot open another users notification', function () {
    $admin = notificationUser('admin');
    $fieldStaff = notificationUser('employee');
    $admin->notify(new InAppNotification([
        'title' => 'Private admin notice',
        'message' => 'For administrators only.',
        'kind' => 'announcement',
        'action_route' => 'notifications.index',
        'action_parameters' => [],
        'action_module' => null,
        'event_key' => null,
        'audience_label' => 'Administrators',
    ]));
    $notification = $admin->fresh()->notifications->first();

    $this->actingAs($fieldStaff)
        ->post(route('notifications.broadcast'), [
            'title' => 'Unauthorized',
            'message' => 'This should not be delivered.',
            'audience' => 'all',
        ])
        ->assertForbidden();

    $this->actingAs($fieldStaff)
        ->get(route('notifications.open', $notification->id))
        ->assertNotFound();
});

test('notification center and feed are available to every authenticated user', function () {
    $fieldStaff = notificationUser('employee');
    $fieldStaff->notify(new InAppNotification([
        'title' => 'Site update',
        'message' => 'A new project update is available.',
        'kind' => 'project',
        'action_route' => 'dashboard',
        'action_parameters' => [],
        'action_module' => null,
        'event_key' => null,
        'audience_label' => 'All departments',
    ]));

    $this->actingAs($fieldStaff)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Notification Center')
        ->assertSee('Site update');

    $this->actingAs($fieldStaff)
        ->getJson(route('notifications.feed'))
        ->assertOk()
        ->assertJsonPath('unread_count', 1)
        ->assertJsonPath('notifications.0.title', 'Site update');
});
