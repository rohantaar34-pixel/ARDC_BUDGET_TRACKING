<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use App\Notifications\InAppNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NotificationBroadcaster
{
    public function projectCreated(Project $project, ?User $actor): int
    {
        return $this->toAll(
            title: 'New project added',
            message: $this->actorPrefix($actor)."added the {$project->name} project.",
            kind: 'project',
            actionRoute: 'dashboard',
            eventKey: "project-created-{$project->id}",
            audienceLabel: 'All departments',
        );
    }

    public function documentAdded(Document $document, ?User $actor): int
    {
        return $this->toModules(
            modules: [User::MODULE_DOCUMENTS],
            title: 'New file added',
            message: $this->actorPrefix($actor)."added {$document->title} to Document Tracker.",
            kind: 'document',
            actionRoute: 'documents.show',
            actionParameters: ['document' => $document->id],
            eventKey: "document-created-{$document->id}",
            audienceLabel: 'Document Tracker',
        );
    }

    public function toAll(
        string $title,
        string $message,
        string $kind = 'announcement',
        ?string $actionRoute = 'notifications.index',
        array $actionParameters = [],
        ?string $eventKey = null,
        string $audienceLabel = 'All departments',
    ): int {
        return $this->send(
            User::query()->with('accessRole')->get(),
            $this->payload(
                $title,
                $message,
                $kind,
                $actionRoute,
                $actionParameters,
                null,
                $eventKey,
                $audienceLabel,
            ),
        );
    }

    public function toModules(
        array $modules,
        string $title,
        string $message,
        string $kind = 'announcement',
        ?string $actionRoute = 'notifications.index',
        array $actionParameters = [],
        ?string $eventKey = null,
        ?string $audienceLabel = null,
    ): int {
        $modules = array_values(array_unique(array_map('strval', $modules)));
        $recipients = User::query()
            ->with('accessRole')
            ->get()
            ->filter(fn (User $user) => $user->hasAnyModuleAccess($modules))
            ->values();

        return $this->send(
            $recipients,
            $this->payload(
                $title,
                $message,
                $kind,
                $actionRoute,
                $actionParameters,
                $modules[0] ?? null,
                $eventKey,
                $audienceLabel ?? $this->moduleAudienceLabel($modules),
            ),
        );
    }

    private function send(Collection $recipients, array $payload): int
    {
        $sent = 0;

        foreach ($recipients as $recipient) {
            try {
                if ($this->alreadySent($recipient, $payload['event_key'] ?? null)) {
                    continue;
                }

                $recipient->notify(new InAppNotification($payload));
                $sent++;
            } catch (\Throwable $exception) {
                Log::warning('Unable to deliver in-app notification.', [
                    'user_id' => $recipient->id,
                    'event_key' => $payload['event_key'] ?? null,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    private function alreadySent(User $recipient, ?string $eventKey): bool
    {
        if (!$eventKey) {
            return false;
        }

        return $recipient->notifications()
            ->where('type', InAppNotification::class)
            ->where('data->event_key', $eventKey)
            ->exists();
    }

    private function payload(
        string $title,
        string $message,
        string $kind,
        ?string $actionRoute,
        array $actionParameters,
        ?string $actionModule,
        ?string $eventKey,
        string $audienceLabel,
    ): array {
        return [
            'title' => $title,
            'message' => $message,
            'kind' => $kind,
            'action_route' => $actionRoute,
            'action_parameters' => $actionParameters,
            'action_module' => $actionModule,
            'event_key' => $eventKey,
            'audience_label' => $audienceLabel,
        ];
    }

    private function actorPrefix(?User $actor): string
    {
        return $actor ? "{$actor->name} " : 'A user ';
    }

    private function moduleAudienceLabel(array $modules): string
    {
        $catalog = User::moduleCatalog();
        $labels = collect($modules)
            ->map(fn (string $module) => $catalog[$module]['label'] ?? $module)
            ->unique()
            ->values();

        return $labels->isEmpty() ? 'Selected departments' : $labels->join(', ');
    }
}
