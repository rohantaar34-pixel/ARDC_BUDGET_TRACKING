<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class DocumentExpiryNotifier
{
    public function __construct(private readonly NotificationBroadcaster $broadcaster)
    {
    }

    public function sendUpcoming(?CarbonInterface $today = null): int
    {
        $today = CarbonImmutable::instance($today ?? now())->startOfDay();
        $sent = 0;

        Document::query()
            ->active()
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $today->toDateString())
            ->whereDate('expiry_date', '<=', $today->addDays(30)->toDateString())
            ->orderBy('expiry_date')
            ->each(function (Document $document) use ($today, &$sent): void {
                $sent += $this->sendForDocument($document, $today);
            });

        return $sent;
    }

    public function sendForDocument(Document $document, ?CarbonInterface $today = null): int
    {
        if (!$document->expiry_date || $document->status !== 'active') {
            return 0;
        }

        $today = CarbonImmutable::instance($today ?? now())->startOfDay();
        $expiry = CarbonImmutable::instance($document->expiry_date)->startOfDay();
        $days = (int) $today->diffInDays($expiry, false);

        if ($days < 0 || $days > 30) {
            return 0;
        }

        $date = $expiry->format('F j, Y');
        $timing = match ($days) {
            0 => "expires today, {$date}",
            1 => "expires tomorrow, {$date}",
            default => "expires in {$days} days on {$date}",
        };

        return $this->broadcaster->toModules(
            modules: [User::MODULE_DOCUMENTS],
            title: 'Document expiry reminder',
            message: "{$document->title} {$timing}.",
            kind: 'expiry',
            actionRoute: 'documents.show',
            actionParameters: ['document' => $document->id],
            eventKey: "document-expiry-{$document->id}-{$expiry->toDateString()}",
            audienceLabel: 'Document Tracker',
        );
    }
}
