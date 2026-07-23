@extends('layouts.app')

@section('title', 'Notifications - ARDC')

@section('content')
<style>
    .notification-page {
        --notice-ink: #14213d;
        --notice-muted: #64748b;
        --notice-border: #dce3ec;
        max-width: 1040px;
        margin: 0 auto;
    }

    .notification-hero {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 22px;
        padding: 24px;
        border-radius: 20px;
        background:
            radial-gradient(circle at 88% 10%, rgba(255, 255, 255, .18), transparent 34%),
            linear-gradient(125deg, #1d4ed8, #0f9f8f);
        color: #fff;
        box-shadow: 0 18px 45px rgba(153, 27, 27, .2);
    }

    .notification-hero h1 { margin: 0 0 6px; font-size: clamp(24px, 4vw, 36px); font-weight: 900; }
    .notification-hero p { margin: 0; max-width: 620px; color: rgba(255, 255, 255, .82); font-size: 13px; line-height: 1.6; }
    .notification-read-form { flex: 0 0 auto; }
    .notification-read-all { min-height: 42px; padding: 10px 15px; border: 1px solid rgba(255, 255, 255, .45); border-radius: 11px; background: rgba(255, 255, 255, .14); color: #fff; font-size: 12px; font-weight: 850; cursor: pointer; }

    .notification-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(300px, .75fr);
        gap: 20px;
        align-items: start;
    }

    .notification-card {
        overflow: hidden;
        border: 1px solid var(--notice-border);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 12px 34px rgba(15, 23, 42, .06);
    }

    .notification-card-head { padding: 18px 20px; border-bottom: 1px solid var(--notice-border); }
    .notification-card-head h2 { margin: 0 0 4px; color: var(--notice-ink); font-size: 17px; font-weight: 900; }
    .notification-card-head p { margin: 0; color: var(--notice-muted); font-size: 11px; line-height: 1.5; }
    .notification-list { display: grid; }

    .notification-row {
        display: grid;
        grid-template-columns: 42px minmax(0, 1fr);
        gap: 12px;
        padding: 17px 20px;
        border-bottom: 1px solid #edf1f5;
        color: inherit;
        text-decoration: none;
        transition: background .16s ease;
    }

    .notification-row:last-child { border-bottom: 0; }
    .notification-row:hover { background: #f8fafc; }
    .notification-row.unread { background: #fff8f1; }
    .notification-row.unread:hover { background: #fff3e5; }
    .notice-icon { display: grid; width: 42px; height: 42px; place-items: center; border-radius: 12px; background: #e2e8f0; color: #475569; font-size: 16px; font-weight: 900; }
    .notice-icon.project { background: #ffedd5; color: #c2410c; }
    .notice-icon.document { background: #d1fae5; color: #047857; }
    .notice-icon.expiry { background: #fee2e2; color: #b91c1c; }
    .notice-icon.announcement { background: #dbeafe; color: #1d4ed8; }
    .notice-title-line { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
    .notice-title { margin: 0; color: var(--notice-ink); font-size: 13px; font-weight: 850; line-height: 1.4; }
    .notice-dot { width: 8px; height: 8px; flex: 0 0 auto; margin-top: 5px; border-radius: 50%; background: #0f9f8f; }
    .notice-message { margin: 4px 0 7px; color: #475569; font-size: 12px; line-height: 1.55; }
    .notice-meta { display: flex; flex-wrap: wrap; gap: 6px 10px; color: #94a3b8; font-size: 10px; font-weight: 650; }
    .notification-empty { padding: 42px 20px; color: var(--notice-muted); text-align: center; font-size: 13px; }
    .notification-pagination { padding: 14px 18px; border-top: 1px solid var(--notice-border); }

    .broadcast-form { display: grid; gap: 14px; padding: 18px 20px 21px; }
    .broadcast-label { display: block; margin-bottom: 6px; color: #334155; font-size: 11px; font-weight: 850; }
    .broadcast-input { width: 100%; border: 1px solid #cbd5e1; border-radius: 11px; padding: 11px 12px; background: #fff; color: #0f172a; font-size: 13px; outline: none; }
    .broadcast-input:focus { border-color: #0f9f8f; box-shadow: 0 0 0 3px rgba(15, 159, 143, .1); }
    textarea.broadcast-input { min-height: 110px; resize: vertical; line-height: 1.5; }
    .audience-options { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .audience-option, .module-option { display: flex; align-items: flex-start; gap: 8px; padding: 10px; border: 1px solid #dbe2ea; border-radius: 10px; background: #f8fafc; color: #334155; font-size: 11px; font-weight: 750; cursor: pointer; }
    .audience-option input, .module-option input { width: 16px; height: 16px; flex: 0 0 auto; accent-color: #0f9f8f; }
    .module-options { display: grid; gap: 7px; max-height: 220px; overflow-y: auto; padding-right: 3px; }
    .module-options[hidden] { display: none; }
    .broadcast-error { margin-top: 5px; color: #b91c1c; font-size: 10px; font-weight: 750; }
    .broadcast-submit { min-height: 43px; border: 0; border-radius: 11px; background: #0f766e; color: #fff; font-size: 12px; font-weight: 900; cursor: pointer; }
    .notification-guide { margin-top: 14px; padding: 14px 15px; border: 1px solid #fde68a; border-radius: 13px; background: #fffbeb; color: #78350f; font-size: 11px; line-height: 1.6; }

    @media (max-width: 820px) {
        .notification-layout { grid-template-columns: 1fr; }
        .notification-compose { order: -1; }
    }

    @media (max-width: 560px) {
        .notification-hero { align-items: stretch; flex-direction: column; padding: 20px; }
        .notification-read-all { width: 100%; }
        .notification-row { grid-template-columns: 36px minmax(0, 1fr); padding: 15px 14px; }
        .notice-icon { width: 36px; height: 36px; border-radius: 10px; }
        .audience-options { grid-template-columns: 1fr; }
    }
</style>

<div class="notification-page">
    <section class="notification-hero">
        <div>
            <h1>Notification Center</h1>
            <p>Project, document, expiry, and department announcements appear here. Select any notice to mark it read and open its related record.</p>
        </div>
        @if(auth()->user()->unreadNotifications()->exists())
            <form method="POST" action="{{ route('notifications.read-all') }}" class="notification-read-form">
                @csrf
                <button type="submit" class="notification-read-all">Mark all as read</button>
            </form>
        @endif
    </section>

    <div class="notification-layout">
        <section class="notification-card">
            <header class="notification-card-head">
                <h2>Your notifications</h2>
                <p>Only notices intended for your assigned departments and modules are shown.</p>
            </header>

            <div class="notification-list">
                @forelse($notifications as $notification)
                    @php
                        $kind = $notification->data['kind'] ?? 'announcement';
                        $icon = match($kind) {
                            'project' => 'P',
                            'document' => 'D',
                            'expiry' => '!',
                            default => 'i',
                        };
                    @endphp
                    <a href="{{ route('notifications.open', $notification->id) }}" class="notification-row {{ $notification->read_at ? '' : 'unread' }}">
                        <span class="notice-icon {{ $kind }}" aria-hidden="true">{{ $icon }}</span>
                        <span>
                            <span class="notice-title-line">
                                <span class="notice-title">{{ $notification->data['title'] ?? 'Notification' }}</span>
                                @if(!$notification->read_at)<span class="notice-dot" title="Unread"></span>@endif
                            </span>
                            <span class="notice-message">{{ $notification->data['message'] ?? '' }}</span>
                            <span class="notice-meta">
                                <span>{{ $notification->created_at?->diffForHumans() }}</span>
                                @if(!empty($notification->data['audience_label']))
                                    <span>{{ $notification->data['audience_label'] }}</span>
                                @endif
                            </span>
                        </span>
                    </a>
                @empty
                    <div class="notification-empty">No notifications yet.</div>
                @endforelse
            </div>

            @if($notifications->hasPages())
                <div class="notification-pagination">{{ $notifications->links() }}</div>
            @endif
        </section>

        <aside>
            @if(auth()->user()->canManageUsers())
                <section class="notification-card notification-compose">
                    <header class="notification-card-head">
                        <h2>Broadcast announcement</h2>
                        <p>Send an internal notice to all users or selected module departments.</p>
                    </header>
                    <form method="POST" action="{{ route('notifications.broadcast') }}" class="broadcast-form" id="broadcastForm">
                        @csrf
                        <label>
                            <span class="broadcast-label">Title *</span>
                            <input class="broadcast-input" name="title" value="{{ old('title') }}" maxlength="120" required>
                            @error('title')<span class="broadcast-error">{{ $message }}</span>@enderror
                        </label>
                        <label>
                            <span class="broadcast-label">Message *</span>
                            <textarea class="broadcast-input" name="message" maxlength="1000" required>{{ old('message') }}</textarea>
                            @error('message')<span class="broadcast-error">{{ $message }}</span>@enderror
                        </label>
                        <fieldset>
                            <legend class="broadcast-label">Audience *</legend>
                            <div class="audience-options">
                                <label class="audience-option">
                                    <input type="radio" name="audience" value="all" {{ old('audience', 'all') === 'all' ? 'checked' : '' }}>
                                    All departments
                                </label>
                                <label class="audience-option">
                                    <input type="radio" name="audience" value="modules" {{ old('audience') === 'modules' ? 'checked' : '' }}>
                                    Selected modules
                                </label>
                            </div>
                        </fieldset>
                        <div class="module-options" id="broadcastModules" {{ old('audience', 'all') === 'modules' ? '' : 'hidden' }}>
                            @foreach($moduleOptions as $module => $details)
                                <label class="module-option">
                                    <input type="checkbox" name="modules[]" value="{{ $module }}" {{ in_array($module, old('modules', []), true) ? 'checked' : '' }}>
                                    <span>{{ $details['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('modules')<span class="broadcast-error">{{ $message }}</span>@enderror
                        <button type="submit" class="broadcast-submit">Send announcement</button>
                    </form>
                </section>
            @endif

            <div class="notification-guide">
                <strong>How it works:</strong> Project notices go to all departments. File and expiry notices go to users assigned to Document Tracker. Expiry checks run daily and notify once per document expiry date.
            </div>
        </aside>
    </div>
</div>

<script>
    (() => {
        const form = document.getElementById('broadcastForm');
        const modules = document.getElementById('broadcastModules');
        if (!form || !modules) return;

        form.addEventListener('change', event => {
            if (event.target.name === 'audience') {
                modules.hidden = event.target.value !== 'modules';
            }
        });
    })();
</script>
@endsection
