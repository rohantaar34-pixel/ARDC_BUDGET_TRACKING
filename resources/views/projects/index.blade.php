{{-- resources/views/projects/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <style>
        /* Enhanced Dashboard Button Styles */
        .btn-dashboard-enhanced {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--indigo) 0%, #4f46e5 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-dashboard-enhanced::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-dashboard-enhanced:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-dashboard-enhanced:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
        }

        .btn-dashboard-enhanced:active {
            transform: translateY(0);
        }

        .btn-dashboard-enhanced svg {
            stroke: white;
            transition: transform 0.3s ease;
        }

        .btn-dashboard-enhanced:hover svg {
            transform: scale(1.1);
        }

        /* Dashboard Badge */
        .dashboard-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(99, 102, 241, 0.1);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            color: var(--indigo);
            margin-left: 10px;
        }

        /* Floating Dashboard Button (Optional) */
        .floating-dashboard {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(99, 102, 241, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(99, 102, 241, 0);
            }
        }

        /* Dashboard Quick Stats Dropdown */
        .dashboard-quick-stats {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            padding: 12px;
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .dashboard-btn-wrapper:hover .dashboard-quick-stats {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            pointer-events: auto;
        }

        .quick-stat-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 12px;
            font-size: 13px;
            border-bottom: 1px solid var(--border);
        }

        .quick-stat-item:last-child {
            border-bottom: none;
        }

        .quick-stat-label {
            color: var(--ink-4);
        }

        .quick-stat-value {
            font-weight: 700;
            color: var(--indigo);
        }

        :root {
            --indigo: #6366f1;
            --indigo-dark: #4f46e5;
            --indigo-light: #eef2ff;
            --green: #059669;
            --red: #dc2626;
            --orange: #ea580c;
            --ink: #111827;
            --ink-2: #374151;
            --ink-3: #6b7280;
            --ink-4: #9ca3af;
            --border: #e8e8ed;
            --bg: #f8f8fb;
            --white: #ffffff;
            --radius: 14px;
            --radius-sm: 9px;
        }

        * {
            box-sizing: border-box;
        }

        .app {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--bg);
            min-height: 100vh;
            color: var(--ink);
        }

        /* ── Page header ── */
        .page-header {
            padding: 24px 20px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .page-title {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -.03em;
            margin: 0;
        }

        .page-sub {
            font-size: 13px;
            color: var(--ink-4);
            margin: 3px 0 0;
        }

        /* ── Stat strip ── */
        .stat-strip {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding: 20px 20px 0;
        }

        .stat-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px 18px;
        }

        .stat-card.full {
            grid-column: 1 / -1;
        }

        .stat-lbl {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: var(--ink-4);
            margin-bottom: 5px;
        }

        .stat-val {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -.03em;
            line-height: 1;
        }

        .c-indigo {
            color: var(--indigo);
        }

        .c-green {
            color: var(--green);
        }

        .c-red {
            color: var(--red);
        }

        .c-orange {
            color: var(--orange);
        }

        .stat-meta {
            font-size: 11px;
            color: var(--ink-4);
            margin-top: 4px;
        }

        /* ── Section label ── */
        .section-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--ink-4);
            padding: 24px 20px 10px;
        }

        /* ── Project cards ── */
        .project-list {
            padding: 0 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .project-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            color: inherit;
            transition: box-shadow .15s, transform .1s;
            -webkit-tap-highlight-color: transparent;
        }

        .project-card:active {
            transform: scale(.99);
        }

        .proj-icon {
            width: 42px;
            height: 42px;
            background: var(--indigo-light);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .proj-body {
            flex: 1;
            min-width: 0;
        }

        .proj-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--ink);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 4px;
        }

        .proj-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            font-size: 11px;
            color: var(--ink-4);
        }

        .proj-meta-item {
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .budget-bar {
            height: 3px;
            background: #f0f0f5;
            border-radius: 99px;
            margin-top: 7px;
        }

        .budget-fill {
            height: 3px;
            background: var(--indigo);
            border-radius: 99px;
            transition: width .4s ease;
        }

        .proj-balance {
            text-align: right;
            flex-shrink: 0;
        }

        .proj-balance-lbl {
            font-size: 10px;
            color: var(--ink-4);
            margin-bottom: 3px;
        }

        .proj-balance-val {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        /* ── FAB / create button ── */
        .fab-wrap {
            padding: 20px 20px 0;
        }

        .fab-toggle {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px;
            background: var(--indigo);
            color: #fff;
            border: none;
            border-radius: var(--radius);
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
            -webkit-tap-highlight-color: transparent;
        }

        .fab-toggle:hover {
            background: var(--indigo-dark);
        }

        /* ── Slide-down create form ── */
        .create-form-wrap {
            margin: 12px 20px 0;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transition: max-height .3s ease, opacity .25s ease, padding .3s ease;
        }

        .create-form-wrap.open {
            max-height: 600px;
            opacity: 1;
        }

        .create-form {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .field-lbl {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--ink-3);
            margin-bottom: 5px;
        }

        .field-in {
            width: 100%;
            padding: 11px 14px;
            font-size: 14px;
            border: 1px solid #e5e7eb;
            border-radius: var(--radius-sm);
            background: #fafafa;
            color: var(--ink);
            outline: none;
            transition: border-color .15s, background .15s;
            font-family: inherit;
        }

        .field-in:focus {
            border-color: var(--indigo);
            background: var(--white);
        }

        .btn-create {
            width: 100%;
            padding: 13px;
            background: var(--indigo);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: background .15s;
            font-family: inherit;
        }

        .btn-create:hover {
            background: var(--indigo-dark);
        }

        /* ── Empty state ── */
        .empty-state {
            text-align: center;
            padding: 52px 20px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
        }

        .empty-state p {
            font-size: 14px;
            color: var(--ink-4);
            margin: 10px 0 0;
        }

        /* ── Bottom padding for mobile ── */
        .page-bottom {
            height: 32px;
        }

        /* ── Tablet+ ── */
        @media (min-width: 640px) {
            .page-header {
                padding: 32px 32px 0;
            }

            .stat-strip {
                padding: 24px 32px 0;
                grid-template-columns: repeat(4, 1fr);
            }

            .stat-card.full {
                grid-column: auto;
            }

            .section-label {
                padding: 28px 32px 12px;
            }

            .project-list {
                padding: 0 32px;
            }

            .fab-wrap {
                padding: 24px 32px 0;
            }

            .create-form-wrap {
                margin: 12px 32px 0;
            }

            .page-title {
                font-size: 24px;
            }
        }

        @media (min-width: 1024px) {
            .main-layout {
                display: block;
                padding: 0 32px;
            }

            .fab-wrap,
            .create-form-wrap,
            .section-label,
            .project-list {
                padding-left: 0;
                padding-right: 0;
                margin-left: 0;
                margin-right: 0;
            }

            .create-form-wrap {
                margin: 12px 0 0;
            }

            .sidebar {
                padding-top: 24px;
            }

            .main-col {
                padding-top: 24px;
            }
        }
    </style>

    <div class="app">
        {{-- Back to Dashboard --}}
        {{-- Enhanced Dashboard Button with Stats --}}
        {{-- Simple Dashboard Button --}}
        <a href="{{ route('dashboard') }}" class="btn-dashboard-enhanced">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round">
                <rect x="3" y="3" width="7" height="7" />
                <rect x="14" y="3" width="7" height="7" />
                <rect x="14" y="14" width="7" height="7" />
                <rect x="3" y="14" width="7" height="7" />
            </svg>
            Dashboard
        </a>
        {{-- Header --}}
        <div class="page-header">
            <div>
                <h1 class="page-title">Ledger</h1>
                <p class="page-sub">Projects &amp; finances</p>
            </div>
            <div
                style="width:36px;height:36px;background:var(--indigo-light);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--indigo)"
                    stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" />
                    <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                </svg>
            </div>
        </div>

        {{-- Stats --}}
        <div class="stat-strip">
            <div class="stat-card">
                <div class="stat-lbl">Projects</div>
                <div class="stat-val c-indigo">{{ $summary['total_projects'] }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-lbl">Total Budget</div>
                <div class="stat-val c-indigo">₱{{ number_format($summary['total_budget'] ?? 0, 0) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-lbl">Budget Additions</div>
                <div class="stat-val c-green">₱{{ number_format($summary['total_budget_additions'] ?? 0, 0) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-lbl">Expenses</div>
                <div class="stat-val c-red">₱{{ number_format($summary['total_expense'], 0) }}</div>
            </div>
        </div>

        {{-- Desktop layout wrapper --}}
        <div class="main-layout" style="margin-top:0;">

            {{-- Sidebar: create form --}}


            {{-- Main col: project list --}}
            <div class="main-col">
                <div class="section-label">All Projects ({{ $projects->count() }})</div>
                <div class="project-list">

                    @forelse($projects as $project)
                        <a href="{{ route('projects.show', $project) }}" class="project-card">
                            <div class="proj-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                    stroke="var(--indigo)" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" />
                                </svg>
                            </div>
                            <div class="proj-body">
                                <div class="proj-name">{{ $project->name }}</div>
                                <div class="proj-meta">
                                    <span class="proj-meta-item" style="color:var(--green);">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                                        </svg>
                                        ₱{{ number_format($project->total_budget_additions, 0) }}
                                    </span>
                                    <span class="proj-meta-item" style="color:var(--red);">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                            <polyline points="23 18 13.5 8.5 8.5 13.5 1 6" />
                                        </svg>
                                        ₱{{ number_format($project->total_expense, 0) }}
                                    </span>
                                    @php $totalBudget = $project->budget + $project->total_budget_additions; @endphp
                                    @if ($totalBudget > 0)
                                        <span class="proj-meta-item">{{ $project->budget_utilization }}% used</span>
                                    @endif
                                </div>
                                @php $totalBudgetForBar = $project->budget + $project->total_budget_additions; @endphp
                                @if ($totalBudgetForBar > 0)
                                    <div class="budget-bar">
                                        <div class="budget-fill"
                                            style="width:{{ min($project->budget_utilization, 100) }}%"></div>
                                    </div>
                                @endif
                            </div>
                            <div class="proj-balance">
                                <div class="proj-balance-lbl">Balance</div>
                                <div
                                    class="proj-balance-val {{ $project->current_budget >= 0 ? 'c-indigo' : 'c-orange' }}">
                                    ₱{{ number_format($project->current_budget, 0) }}
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="empty-state">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none"
                                stroke="var(--indigo)" stroke-width="1.5" stroke-linecap="round"
                                style="margin:0 auto;display:block;opacity:.4;">
                                <rect x="2" y="7" width="20" height="14" rx="2" />
                                <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                                <line x1="12" y1="11" x2="12" y2="17" />
                                <line x1="9" y1="14" x2="15" y2="14" />
                            </svg>
                            <p>No projects yet — tap New Project to start.</p>
                        </div>
                    @endforelse

                </div>
            </div>

        </div>{{-- /main-layout --}}

        <div class="page-bottom"></div>
    </div>

    <script>
        // Form logic removed
    </script>
@endsection
