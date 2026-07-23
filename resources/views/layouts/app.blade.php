{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="true">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>GEO CORP. Project Budget Tracking</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            font-family: 'Montserrat', sans-serif;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            letter-spacing: -0.015em;
        }

        :root {
            --geo-blue: #1d4ed8;
            --geo-blue-dark: #153e75;
            --geo-blue-soft: #eff6ff;
            --geo-teal: #0f9f8f;
            --geo-teal-dark: #0f766e;
            --geo-teal-soft: #ecfdf8;
        }

        body {
            background: linear-gradient(135deg, #f8f9fc 0%, #f3f4f8 100%);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        html,
        body {
            touch-action: manipulation;
        }

        input,
        textarea,
        select {
            -webkit-appearance: none;
            appearance: none;
            font-size: 16px;
            font-family: 'Montserrat', sans-serif;
        }

        button {
            -webkit-appearance: none;
            appearance: none;
            font-family: 'Montserrat', sans-serif;
        }

        /* Logout button in nav */
        .nav-logout-form {
            margin: 0;
        }

        .btn-nav-logout {
            padding: 7px 16px;
            background: rgba(255, 255, 255, .15);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .3);
            border-radius: 7px;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            transition: background .15s;
        }

        .btn-nav-logout:hover {
            background: rgba(255, 255, 255, .25);
        }

        .nav-actions {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 10px;
        }

        .nav-user-link {
            display: inline-flex;
            min-width: 0;
            align-items: center;
            gap: 8px;
            padding: 5px 9px 5px 6px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            color: #475569;
            text-decoration: none;
            transition: border-color .18s ease, background .18s ease;
        }

        .nav-user-link:hover {
            border-color: #a7e3d9;
            background: var(--geo-teal-soft);
        }

        .nav-user-avatar {
            display: grid;
            width: 28px;
            height: 28px;
            flex: 0 0 auto;
            place-items: center;
            border-radius: 8px;
            background: #d9f6f1;
            color: var(--geo-teal-dark);
            font-size: 11px;
            font-weight: 900;
        }

        .nav-user-name {
            max-width: 170px;
            overflow: hidden;
            font-size: .72rem;
            font-weight: 750;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .notification-nav {
            position: relative;
            flex: 0 0 auto;
        }

        .notification-bell {
            position: relative;
            display: grid;
            width: 40px;
            height: 40px;
            place-items: center;
            border: 1px solid #e2e8f0;
            border-radius: 11px;
            background: #fff;
            color: #475569;
            cursor: pointer;
            transition: border-color .18s ease, background .18s ease, color .18s ease;
        }

        .notification-bell:hover,
        .notification-bell[aria-expanded="true"] {
            border-color: #99dacf;
            background: var(--geo-teal-soft);
            color: var(--geo-teal-dark);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            display: grid;
            min-width: 19px;
            height: 19px;
            padding: 0 5px;
            place-items: center;
            border: 2px solid #fff;
            border-radius: 999px;
            background: var(--geo-teal);
            color: #fff;
            font-size: 9px;
            font-weight: 900;
            line-height: 1;
        }

        .notification-badge[hidden] { display: none; }

        .notification-panel {
            position: absolute;
            z-index: 60;
            top: calc(100% + 12px);
            right: 0;
            display: none;
            width: min(390px, calc(100vw - 24px));
            overflow: hidden;
            border: 1px solid #dce3ec;
            border-radius: 17px;
            background: #fff;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .22);
        }

        .notification-panel.open {
            display: block;
            animation: notification-panel-in .16s ease-out;
        }

        .notification-panel-head,
        .notification-panel-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 13px 15px;
            background: #f8fafc;
        }

        .notification-panel-head { border-bottom: 1px solid #e2e8f0; }
        .notification-panel-foot { border-top: 1px solid #e2e8f0; }
        .notification-panel-title { margin: 0; color: #0f172a; font-size: 13px; font-weight: 900; }
        .notification-panel-link,
        .notification-mark-all {
            border: 0;
            background: transparent;
            color: var(--geo-blue);
            font-size: 10px;
            font-weight: 850;
            text-decoration: none;
            cursor: pointer;
        }

        .notification-panel-list {
            max-height: min(430px, calc(100vh - 190px));
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .notification-panel-item {
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr);
            gap: 10px;
            padding: 13px 15px;
            border-bottom: 1px solid #edf1f5;
            color: inherit;
            text-decoration: none;
            transition: background .15s ease;
        }

        .notification-panel-item:last-child { border-bottom: 0; }
        .notification-panel-item:hover { background: #f8fafc; }
        .notification-panel-item.unread { background: #fff8f1; }
        .notification-panel-item.unread:hover { background: #fff3e5; }
        .notification-panel-icon { display: grid; width: 34px; height: 34px; place-items: center; border-radius: 10px; background: #dbeafe; color: #1d4ed8; font-size: 12px; font-weight: 900; }
        .notification-panel-icon.project { background: #ffedd5; color: #c2410c; }
        .notification-panel-icon.document { background: #d1fae5; color: #047857; }
        .notification-panel-icon.expiry { background: #fee2e2; color: #b91c1c; }
        .notification-panel-copy { min-width: 0; }
        .notification-panel-item-title { display: block; overflow: hidden; color: #0f172a; font-size: 11px; font-weight: 850; line-height: 1.4; text-overflow: ellipsis; white-space: nowrap; }
        .notification-panel-message { display: -webkit-box; margin-top: 2px; overflow: hidden; color: #64748b; font-size: 10px; line-height: 1.45; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
        .notification-panel-time { display: block; margin-top: 5px; color: #94a3b8; font-size: 9px; font-weight: 700; }
        .notification-panel-empty { padding: 35px 18px; color: #64748b; font-size: 11px; text-align: center; }
        .notification-panel-foot .notification-panel-link { width: 100%; text-align: center; }

        @keyframes notification-panel-in {
            from { opacity: 0; transform: translateY(-5px) scale(.99); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @media (max-width: 640px) {
            .nav-actions { gap: 6px; }
            .nav-user-link { padding: 4px; border: 0; background: transparent; }
            .nav-user-name { display: none; }
            .btn-nav-logout { padding: 7px 10px; font-size: .67rem; }
            .notification-bell { width: 36px; height: 36px; }
            .notification-panel {
                position: fixed;
                top: 72px;
                right: 10px;
                left: 10px;
                width: auto;
                max-height: calc(100vh - 84px);
            }
            .notification-panel-list { max-height: calc(100vh - 190px); }
        }

        .app-confirm-overlay {
            display: none;
            position: fixed;
            z-index: 9999;
            inset: 0;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, .62);
            backdrop-filter: blur(3px);
        }

        .app-confirm-overlay.open { display: flex; }

        .app-confirm-card {
            width: min(440px, 100%);
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 28px 70px rgba(15, 23, 42, .3);
            animation: app-confirm-in .18s ease-out;
        }

        .app-confirm-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 20px 20px 13px;
        }

        .app-confirm-icon {
            display: grid;
            width: 38px;
            height: 38px;
            flex: 0 0 auto;
            place-items: center;
            border-radius: 11px;
            background: #fee2e2;
            color: #dc2626;
            font-size: 20px;
            font-weight: 900;
        }

        .app-confirm-title-wrap { display: flex; min-width: 0; align-items: center; gap: 12px; }
        .app-confirm-title { margin: 0; color: #111827; font-size: 17px; line-height: 1.3; }
        .app-confirm-close { display: grid; width: 34px; height: 34px; flex: 0 0 auto; place-items: center; border: 0; border-radius: 9px; background: #f1f5f9; color: #64748b; font-size: 20px; cursor: pointer; }
        .app-confirm-message { margin: 0; padding: 0 20px 20px; color: #64748b; font-size: 13px; line-height: 1.65; overflow-wrap: anywhere; }

        .app-confirm-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 14px 20px;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .app-confirm-btn { min-height: 40px; padding: 9px 15px; border: 0; border-radius: 10px; font-family: 'Montserrat', sans-serif; font-size: 12px; font-weight: 850; cursor: pointer; }
        .app-confirm-cancel { background: #e2e8f0; color: #334155; }
        .app-confirm-submit { background: #dc2626; color: #fff; }

        @keyframes app-confirm-in {
            from { opacity: 0; transform: translateY(8px) scale(.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @media (max-width: 480px) {
            .app-confirm-overlay { align-items: flex-end; padding: 12px; }
            .app-confirm-card { border-radius: 18px; }
            .app-confirm-actions { display: grid; grid-template-columns: 1fr 1fr; }
            .app-confirm-btn { width: 100%; }
        }
    </style>
</head>

<body>
    @php
        $currentUser = Auth::user();
        $homeRoute = $currentUser ? route($currentUser->landingRouteName()) : route('login');
        $settingsRoute = null;
        $headerNotifications = collect();
        $headerUnreadCount = 0;

        if ($currentUser?->canManageUsers()) {
            $settingsRoute = route('settings.users.index');
        } elseif ($currentUser?->canManageProjectsSettings()) {
            $settingsRoute = route('settings.projects.index');
        }

        if ($currentUser) {
            $headerNotifications = $currentUser->notifications()->latest()->limit(8)->get();
            $headerUnreadCount = $currentUser->unreadNotifications()->count();
        }
    @endphp

    <div class="min-h-screen flex flex-col">

        <!-- Navigation Bar -->
        <nav class="bg-white sticky top-0 z-40 shadow-sm" style="border-bottom: 4px solid var(--geo-teal);">
            <div class="w-full px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between">

                <a href="{{ $homeRoute }}"
                    class="flex items-center gap-2 sm:gap-3 flex-shrink-0 hover:opacity-80 transition-opacity">
                    <img src="{{ asset('images/logo.jpg') }}" alt="GEO CORP. Logo"
                        class="h-10 sm:h-12 w-auto object-contain">
                    <div class="hidden sm:flex flex-col">
                        <span class="text-sm font-black text-slate-900">GEO CORP.</span>
                        <p class="text-xs font-bold leading-tight" style="color: var(--geo-teal-dark);">Budget Tracking</p>
                    </div>
                </a>

                @auth
                    <div class="nav-actions">
                        <div class="notification-nav" id="notificationNav" data-feed-url="{{ route('notifications.feed') }}">
                            <button type="button" class="notification-bell" id="notificationBell" aria-label="Open notifications" aria-expanded="false">
                                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path>
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                </svg>
                                <span class="notification-badge" id="notificationBadge" {{ $headerUnreadCount ? '' : 'hidden' }}>
                                    {{ $headerUnreadCount > 99 ? '99+' : $headerUnreadCount }}
                                </span>
                            </button>

                            <section class="notification-panel" id="notificationPanel" aria-label="Recent notifications">
                                <header class="notification-panel-head">
                                    <h2 class="notification-panel-title">Notifications</h2>
                                    @if($headerUnreadCount)
                                        <form method="POST" action="{{ route('notifications.read-all') }}">
                                            @csrf
                                            <button type="submit" class="notification-mark-all">Mark all read</button>
                                        </form>
                                    @endif
                                </header>
                                <div class="notification-panel-list" id="notificationPanelList">
                                    @forelse($headerNotifications as $notification)
                                        @php
                                            $kind = $notification->data['kind'] ?? 'announcement';
                                            $icon = match($kind) {
                                                'project' => 'P',
                                                'document' => 'D',
                                                'expiry' => '!',
                                                default => 'i',
                                            };
                                        @endphp
                                        <a href="{{ route('notifications.open', $notification->id) }}" class="notification-panel-item {{ $notification->read_at ? '' : 'unread' }}">
                                            <span class="notification-panel-icon {{ $kind }}" aria-hidden="true">{{ $icon }}</span>
                                            <span class="notification-panel-copy">
                                                <span class="notification-panel-item-title">{{ $notification->data['title'] ?? 'Notification' }}</span>
                                                <span class="notification-panel-message">{{ $notification->data['message'] ?? '' }}</span>
                                                <span class="notification-panel-time">{{ $notification->created_at?->diffForHumans() }}</span>
                                            </span>
                                        </a>
                                    @empty
                                        <div class="notification-panel-empty">No notifications yet.</div>
                                    @endforelse
                                </div>
                                <footer class="notification-panel-foot">
                                    <a href="{{ route('notifications.index') }}" class="notification-panel-link">View notification center</a>
                                </footer>
                            </section>
                        </div>

                        <a href="{{ route('profile.show') }}" class="nav-user-link" title="Open profile and change password">
                            <span class="nav-user-avatar">{{ Str::upper(Str::substr(Auth::user()->name, 0, 1)) }}</span>
                            <span class="nav-user-name">{{ Auth::user()->name }}</span>
                        </a>
                        
                        @if($settingsRoute)
                            <a href="{{ $settingsRoute }}" class="btn-nav-logout" style="background: #eff6ff; border-color: #bfdbfe; color: var(--geo-blue); text-decoration: none;">
                                Settings
                            </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}" class="nav-logout-form">
                            @csrf
                            <button type="submit" class="btn-nav-logout" style="background:var(--geo-blue-dark); border-color:var(--geo-blue-dark);">
                                Logout
                            </button>
                        </form>
                    </div>
                @endauth

            </div>
        </nav>

        <!-- Main Content -->
        <div class="flex-1 w-full px-4 sm:px-6 py-6 sm:py-12">
            <div class="max-w-7xl mx-auto">

                @if (session('success'))
                    <div
                        class="mb-6 sm:mb-8 p-4 px-4 sm:px-6 bg-gradient-to-r from-green-50 to-emerald-100 border-2 border-green-600 text-green-700 rounded-xl flex items-center gap-3 animate-fade-in">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="font-bold text-sm sm:text-base">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div
                        class="mb-6 sm:mb-8 p-4 px-4 sm:px-6 bg-gradient-to-r from-red-50 to-red-100 border-2 border-red-600 text-red-700 rounded-xl flex items-center gap-3 animate-fade-in">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="font-bold text-sm sm:text-base">{{ session('error') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="mb-6 sm:mb-8 p-4 px-4 sm:px-6 bg-gradient-to-r from-red-50 to-red-100 border-2 border-red-600 text-red-700 rounded-xl animate-fade-in">
                        <h4 class="font-bold mb-2 text-sm sm:text-base">Validation Errors:</h4>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <div class="app-confirm-overlay" id="appConfirmModal" aria-hidden="true">
        <div class="app-confirm-card" role="dialog" aria-modal="true" aria-labelledby="appConfirmTitle" aria-describedby="appConfirmMessage">
            <div class="app-confirm-head">
                <div class="app-confirm-title-wrap">
                    <span class="app-confirm-icon" aria-hidden="true">!</span>
                    <h2 class="app-confirm-title" id="appConfirmTitle">Confirm action</h2>
                </div>
                <button type="button" class="app-confirm-close" id="appConfirmClose" aria-label="Close confirmation">&times;</button>
            </div>
            <p class="app-confirm-message" id="appConfirmMessage">Are you sure you want to continue?</p>
            <div class="app-confirm-actions">
                <button type="button" class="app-confirm-btn app-confirm-cancel" id="appConfirmCancel">Cancel</button>
                <button type="button" class="app-confirm-btn app-confirm-submit" id="appConfirmSubmit">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const nav = document.getElementById('notificationNav');
            if (!nav) return;

            const bell = document.getElementById('notificationBell');
            const panel = document.getElementById('notificationPanel');
            const list = document.getElementById('notificationPanelList');
            const badge = document.getElementById('notificationBadge');
            const iconText = { project: 'P', document: 'D', expiry: '!', announcement: 'i' };

            function setOpen(open) {
                panel.classList.toggle('open', open);
                bell.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            function renderItem(notification) {
                const link = document.createElement('a');
                const kind = ['project', 'document', 'expiry', 'announcement'].includes(notification.kind)
                    ? notification.kind
                    : 'announcement';
                link.href = notification.open_url;
                link.className = `notification-panel-item${notification.is_read ? '' : ' unread'}`;

                const icon = document.createElement('span');
                icon.className = `notification-panel-icon ${kind}`;
                icon.setAttribute('aria-hidden', 'true');
                icon.textContent = iconText[kind];

                const copy = document.createElement('span');
                copy.className = 'notification-panel-copy';
                const title = document.createElement('span');
                title.className = 'notification-panel-item-title';
                title.textContent = notification.title;
                const message = document.createElement('span');
                message.className = 'notification-panel-message';
                message.textContent = notification.message;
                const time = document.createElement('span');
                time.className = 'notification-panel-time';
                time.textContent = notification.created_at || '';
                copy.append(title, message, time);
                link.append(icon, copy);

                return link;
            }

            async function refreshNotifications() {
                try {
                    const response = await fetch(nav.dataset.feedUrl, {
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!response.ok) return;

                    const data = await response.json();
                    const unread = Number(data.unread_count || 0);
                    badge.hidden = unread === 0;
                    badge.textContent = unread > 99 ? '99+' : String(unread);
                    list.replaceChildren();

                    if (!data.notifications.length) {
                        const empty = document.createElement('div');
                        empty.className = 'notification-panel-empty';
                        empty.textContent = 'No notifications yet.';
                        list.append(empty);
                        return;
                    }

                    data.notifications.forEach(notification => list.append(renderItem(notification)));
                } catch (error) {
                    // Keep the last successfully loaded notification list when offline.
                }
            }

            bell.addEventListener('click', event => {
                event.stopPropagation();
                const open = !panel.classList.contains('open');
                setOpen(open);
                if (open) refreshNotifications();
            });
            panel.addEventListener('click', event => event.stopPropagation());
            document.addEventListener('click', () => setOpen(false));
            window.addEventListener('keydown', event => {
                if (event.key === 'Escape') setOpen(false);
            });
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) refreshNotifications();
            });
            window.setInterval(refreshNotifications, 30000);
        })();
    </script>

    <script>
        (() => {
            const modal = document.getElementById('appConfirmModal');
            const title = document.getElementById('appConfirmTitle');
            const message = document.getElementById('appConfirmMessage');
            const confirmButton = document.getElementById('appConfirmSubmit');
            const cancelButton = document.getElementById('appConfirmCancel');
            const closeButton = document.getElementById('appConfirmClose');
            let pendingForm = null;
            let pendingSubmitter = null;

            function closeConfirmation() {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
                pendingForm = null;
                pendingSubmitter = null;
                document.body.style.overflow = '';
            }

            function openConfirmation(form, submitter) {
                pendingForm = form;
                pendingSubmitter = submitter || null;
                title.textContent = form.dataset.confirmTitle || 'Confirm action';
                message.textContent = form.dataset.confirmMessage || 'Are you sure you want to continue?';
                confirmButton.textContent = form.dataset.confirmAction || 'Confirm';
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                confirmButton.focus();
            }

            document.addEventListener('submit', event => {
                const form = event.target.closest('form[data-confirm-message]');
                if (!form || form.dataset.confirmApproved === 'true') {
                    if (form) delete form.dataset.confirmApproved;
                    return;
                }

                event.preventDefault();
                openConfirmation(form, event.submitter);
            });

            confirmButton.addEventListener('click', () => {
                if (!pendingForm) return;

                const form = pendingForm;
                const submitter = pendingSubmitter;
                form.dataset.confirmApproved = 'true';
                closeConfirmation();

                if (typeof form.requestSubmit === 'function') {
                    submitter ? form.requestSubmit(submitter) : form.requestSubmit();
                } else {
                    form.submit();
                }
            });

            cancelButton.addEventListener('click', closeConfirmation);
            closeButton.addEventListener('click', closeConfirmation);
            modal.addEventListener('click', event => {
                if (event.target === modal) closeConfirmation();
            });
            window.addEventListener('keydown', event => {
                if (event.key === 'Escape' && modal.classList.contains('open')) closeConfirmation();
            });
        })();
    </script>

    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }

        @media (max-width: 640px) {
            body {
                overflow-x: hidden;
            }
        }
    </style>
</body>

</html>
