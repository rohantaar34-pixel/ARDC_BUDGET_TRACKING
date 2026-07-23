<?php

namespace App\Console\Commands;

use App\Services\DocumentExpiryNotifier;
use Illuminate\Console\Command;

class SendDocumentExpiryNotifications extends Command
{
    protected $signature = 'notifications:document-expiry';

    protected $description = 'Notify Document Tracker users about files expiring within 30 days';

    public function handle(DocumentExpiryNotifier $notifier): int
    {
        $sent = $notifier->sendUpcoming();
        $this->info("Sent {$sent} document expiry notification(s).");

        return self::SUCCESS;
    }
}
