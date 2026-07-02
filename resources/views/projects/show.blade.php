{{-- resources/views/projects/show.blade.php --}}
@extends('layouts.app')

@section('content')
    <style>
        :root {
            --indigo: #6366f1;
            --indigo-dark: #4f46e5;
            --indigo-light: #eef2ff;
            --green: #059669;
            --green-light: #ecfdf5;
            --red: #dc2626;
            --red-light: #fef2f2;
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

        /* Navigation Bar */
        .nav-bar {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .nav-buttons {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-nav {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: var(--radius-sm);
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            font-family: inherit;
        }

        .btn-back {
            background: var(--white);
            border: 1px solid var(--border);
            color: var(--ink-2);
        }

        .btn-back:hover {
            background: var(--bg);
            border-color: var(--indigo);
            color: var(--indigo);
        }

        .btn-dashboard {
            background: var(--indigo-light);
            color: var(--indigo);
        }

        .btn-dashboard:hover {
            background: var(--indigo);
            color: white;
        }

        .btn-ledger {
            background: var(--white);
            border: 1px solid var(--border);
            color: var(--ink-2);
        }

        .btn-ledger:hover {
            background: var(--indigo-light);
            border-color: var(--indigo);
            color: var(--indigo);
        }

        /* Category Selector Styles */
        .category-selector {
            position: relative;
            width: 100%;
        }

        .category-dropdown {
            position: absolute;
            z-index: 1000;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            max-height: 200px;
            overflow-y: auto;
            width: 100%;
            margin-top: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .category-option {
            padding: 10px 14px;
            cursor: pointer;
            transition: background 0.2s;
            font-size: 13px;
            color: var(--ink-2);
            border-bottom: 1px solid var(--border);
        }

        .category-option:last-child {
            border-bottom: none;
        }

        .category-option:hover {
            background: var(--indigo-light) !important;
            color: var(--indigo);
        }

        .category-option.create-new {
            color: var(--green);
            font-weight: 600;
        }

        .category-option.create-new:hover {
            background: var(--green-light) !important;
            color: var(--green);
        }

        /* Category dropdown scrollbar styling */
        .category-dropdown::-webkit-scrollbar {
            width: 6px;
        }

        .category-dropdown::-webkit-scrollbar-track {
            background: var(--bg);
            border-radius: 3px;
        }

        .category-dropdown::-webkit-scrollbar-thumb {
            background: var(--ink-4);
            border-radius: 3px;
        }

        .category-dropdown::-webkit-scrollbar-thumb:hover {
            background: var(--ink-3);
        }

        .sp-header {
            padding: 20px 20px 0;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .sp-back {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 600;
            color: var(--ink-4);
            text-decoration: none;
            margin-bottom: 8px;
            letter-spacing: .03em;
            -webkit-tap-highlight-color: transparent;
        }

        .sp-back:hover {
            color: var(--indigo);
        }

        .sp-title {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -.03em;
            margin: 0 0 3px;
        }

        .sp-desc {
            font-size: 13px;
            color: var(--ink-4);
            margin: 0;
        }

        .btn-edit {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 9px 14px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--white);
            cursor: pointer;
            color: var(--ink-2);
            white-space: nowrap;
            flex-shrink: 0;
            transition: background .12s;
            font-family: inherit;
        }

        .btn-edit:hover {
            background: #f9fafb;
        }

        .stat-strip {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding: 16px 20px 0;
        }

        .stat-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 14px 16px;
        }

        .stat-lbl {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: var(--ink-4);
            margin-bottom: 4px;
        }

        .stat-val {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -.02em;
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

        .stat-sub {
            font-size: 11px;
            color: var(--ink-4);
            margin-top: 3px;
        }

        .mini-bar {
            height: 3px;
            background: #f0f0f5;
            border-radius: 99px;
            margin-top: 7px;
        }

        .mini-fill {
            height: 3px;
            background: var(--indigo);
            border-radius: 99px;
        }

        .tab-shell {
            margin: 16px 20px 0;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .tab-nav {
            display: flex;
            overflow-x: auto;
            border-bottom: 1px solid #f3f4f6;
            padding: 0 4px;
            scrollbar-width: none;
        }

        .tab-nav::-webkit-scrollbar {
            display: none;
        }

        .tab-btn {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 13px 14px;
            font-size: 12px;
            font-weight: 600;
            color: var(--ink-4);
            border: none;
            border-bottom: 2px solid transparent;
            background: none;
            cursor: pointer;
            white-space: nowrap;
            transition: color .12s, border-color .12s;
            margin-bottom: -1px;
            font-family: inherit;
        }

        .tab-btn:hover {
            color: var(--ink-2);
        }

        .tab-btn.active {
            color: var(--indigo);
            border-bottom-color: var(--indigo);
        }

        .add-form {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        @media(max-width:500px) {
            .form-row {
                grid-template-columns: 1fr;
            }
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

        .type-toggle {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border: 1px solid #e5e7eb;
            border-radius: var(--radius-sm);
            overflow: hidden;
        }

        .type-toggle input[type=radio] {
            display: none;
        }

        .type-toggle label {
            padding: 11px 10px;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background .12s, color .12s;
            color: var(--ink-4);
            background: #fafafa;
        }

        .type-toggle input[value=budget_addition]:checked+label {
            background: var(--green-light);
            color: var(--green);
        }

        .type-toggle input[value=expense]:checked+label {
            background: var(--red-light);
            color: var(--red);
        }

        .btn-save {
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

        .btn-save:hover {
            background: var(--indigo-dark);
        }

        /* LEDGER ENHANCED STYLES */
        .ledger-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .ledger-filters {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            background: var(--white);
        }

        .filter-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .category-group {
            border-bottom: 1px solid var(--border);
        }

        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 20px;
            background: var(--white);
            cursor: pointer;
            transition: background 0.2s;
            user-select: none;
        }

        .category-header:hover {
            background: #fafafa;
        }

        .category-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .toggle-icon {
            font-size: 12px;
            color: var(--ink-4);
            transition: transform 0.2s;
        }

        .category-name {
            font-size: 14px;
            color: var(--ink-2);
        }

        .category-count {
            font-size: 12px;
            color: var(--ink-4);
            font-weight: normal;
        }

        .category-total {
            font-size: 14px;
            font-weight: 700;
            color: var(--red);
        }

        .budget-header .category-total {
            color: var(--green);
        }

        .category-content {
            background: #fefefe;
        }

        .ledger-cards {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }

        .ledger-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            border-bottom: 1px solid #f9fafb;
            transition: background .1s;
        }

        .ledger-item:last-child {
            border-bottom: none;
        }

        .ledger-item:hover {
            background: #fafafa;
        }

        .l-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .l-dot.budget_addition {
            background: var(--green);
        }

        .l-dot.expense {
            background: var(--red);
        }

        .l-body {
            flex: 1;
            min-width: 0;
        }

        .l-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .l-meta {
            font-size: 11px;
            color: var(--ink-4);
            margin-top: 2px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .l-right {
            text-align: right;
            flex-shrink: 0;
        }

        .l-amount {
            font-size: 14px;
            font-weight: 700;
        }

        .l-amount.budget_addition {
            color: var(--green);
        }

        .l-amount.expense {
            color: var(--red);
        }

        .transaction-actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            justify-content: flex-end;
        }

        .btn-view-image {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            padding: 4px 6px;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .btn-view-image:hover {
            background: var(--indigo-light);
        }

        .btn-del {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            color: #d1d5db;
            transition: color .12s, background .12s;
            flex-shrink: 0;
        }

        .btn-del:hover {
            color: var(--red);
            background: var(--red-light);
        }

        .empty-ledger {
            padding: 52px 20px;
            text-align: center;
            color: var(--ink-4);
            font-size: 14px;
        }

        .tbadge {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            padding: 2px 8px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 700;
        }

        .tbadge.budget_addition {
            background: var(--green-light);
            color: var(--green);
        }

        .tbadge.expense {
            background: var(--red-light);
            color: var(--red);
        }

        .pagination-wrapper {
            padding: 20px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .pagination-info {
            font-size: 13px;
            color: var(--ink-4);
        }

        .pagination-controls {
            display: flex;
            gap: 8px;
        }

        .pagination-controls button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-cancel {
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--white);
            cursor: pointer;
            color: var(--ink-2);
            font-family: inherit;
        }

        .btn-confirm {
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 700;
            background: var(--indigo);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-family: inherit;
        }

        .btn-confirm:hover {
            background: var(--indigo-dark);
        }

        /* Analysis Section */
        .analysis-pad {
            padding: 20px;
        }

        .section-title {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--ink-4);
            margin: 0 0 14px;
        }

        .cat-item {
            padding: 10px 0 10px 12px;
            border-left: 3px solid transparent;
            border-radius: 0 8px 8px 0;
            margin-bottom: 4px;
            transition: background .12s, border-color .12s;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .cat-item:hover {
            background: #fafafa;
            border-left-color: var(--indigo);
        }

        .cat-left {
            flex: 1;
            min-width: 0;
        }

        .cat-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink-2);
        }

        .cat-bar {
            height: 2px;
            background: #f0f0f5;
            border-radius: 99px;
            margin-top: 5px;
        }

        .cat-fill {
            height: 2px;
            background: var(--indigo);
            border-radius: 99px;
        }

        .cat-pct {
            font-size: 10px;
            color: var(--ink-4);
            margin-top: 3px;
        }

        .cat-amt {
            font-size: 14px;
            font-weight: 800;
            color: var(--red);
            flex-shrink: 0;
        }

        .analysis-divider {
            height: 1px;
            background: #f3f4f6;
            margin: 20px 0;
        }

        .budget-boxes {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 14px;
        }

        .bx {
            background: #fafafa;
            border: 1px solid #f3f4f6;
            border-radius: 10px;
            padding: 14px;
            text-align: center;
        }

        .bx-lbl {
            font-size: 10px;
            color: var(--ink-4);
            margin-bottom: 4px;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .bx-val {
            font-size: 16px;
            font-weight: 800;
            color: var(--ink);
        }

        .bx-val.red {
            color: var(--red);
        }

        /* Modal */
        .modal-bg {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 100;
            align-items: flex-end;
            justify-content: center;
        }

        .modal-bg.open {
            display: flex;
        }

        @media(min-width:520px) {
            .modal-bg {
                align-items: center;
            }
        }

        .modal-box {
            background: var(--white);
            border-radius: var(--radius) var(--radius) 0 0;
            width: 100%;
            max-width: 480px;
            padding: 24px 24px 32px;
            animation: slideUp .22s ease;
        }

        @media(min-width:520px) {
            .modal-box {
                border-radius: var(--radius);
                margin: 16px;
                padding: 28px;
            }
        }

        .modal-box.image-modal {
            max-width: 90%;
            width: auto;
            padding: 20px;
        }

        @keyframes slideUp {
            from {
                transform: translateY(40px);
                opacity: 0;
            }

            to {
                transform: none;
                opacity: 1;
            }
        }

        .modal-handle {
            width: 36px;
            height: 4px;
            background: #e5e7eb;
            border-radius: 99px;
            margin: 0 auto 20px;
        }

        @media(min-width:520px) {
            .modal-handle {
                display: none;
            }
        }

        .modal-title {
            font-size: 16px;
            font-weight: 800;
            margin: 0 0 20px;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .report-buttons {
            display: flex;
            gap: 8px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .btn-report {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 600;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            border: none;
            font-family: inherit;
        }

        .btn-excel {
            background: #1e7e34;
            color: white;
        }

        .btn-excel:hover {
            background: #16632a;
        }

        .btn-pdf {
            background: #dc2626;
            color: white;
        }

        .btn-pdf:hover {
            background: #b91c1c;
        }

        .btn-word {
            background: #2b5797;
            color: white;
        }

        .btn-word:hover {
            background: #1e3a6b;
        }

        @media(min-width:640px) {
            .sp-header {
                padding: 28px 32px 0;
            }

            .stat-strip {
                padding: 20px 32px 0;
                grid-template-columns: repeat(4, 1fr);
            }

            .tab-shell {
                margin: 20px 32px 0;
            }
        }

        .page-bottom {
            height: 40px;
        }
    </style>

    <div class="app">

        {{-- NEW NAVIGATION BAR WITH BACK BUTTONS --}}
        <div class="nav-bar">
            <div class="nav-buttons">
                {{-- Back to Projects (Ledger) --}}
                <a href="{{ route('projects.index') }}" class="btn-nav btn-back">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                    Back to Ledger
                </a>

                {{-- Back to Dashboard --}}
                <a href="{{ route('dashboard') }}" class="btn-nav btn-dashboard">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round">
                        <rect x="3" y="3" width="7" height="7" />
                        <rect x="14" y="3" width="7" height="7" />
                        <rect x="14" y="14" width="7" height="7" />
                        <rect x="3" y="14" width="7" height="7" />
                    </svg>
                    Dashboard
                </a>

                {{-- View All Documents --}}
                <a href="{{ route('documents.index') }}" class="btn-nav btn-ledger">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                    </svg>
                    Documents
                </a>
            </div>

            {{-- Current Project Indicator --}}
            <div style="font-size: 12px; color: var(--ink-4);">
                Current: <strong style="color: var(--indigo);">{{ $project->name }}</strong>
            </div>
        </div>

        {{-- Header --}}
        <div class="sp-header">
            <div style="flex:1;min-width:0;">
                <a href="{{ route('projects.index') }}" class="sp-back">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                    All Projects
                </a>
                <h1 class="sp-title">{{ $project->name }}</h1>
                @if ($project->description)
                    <p class="sp-desc">{{ $project->description }}</p>
                @endif
            </div>
            @if(Auth::user()->isAdmin())
                <button class="btn-edit" onclick="document.getElementById('editModal').classList.add('open')">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                    Edit Project
                </button>
            @endif
        </div>

        {{-- Stats --}}
        <div class="stat-strip">
            <div class="stat-card">
                <div class="stat-lbl">Initial Budget</div>
                <div class="stat-val c-indigo">₱{{ number_format($project->budget, 0) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-lbl">Budget Additions</div>
                <div class="stat-val c-green">₱{{ number_format($budgetAdditions->sum('amount'), 0) }}</div>
                <div class="stat-sub">{{ $budgetAdditions->count() }}
                    {{ Str::plural('payment', $budgetAdditions->count()) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-lbl">Current Budget</div>
                <div class="stat-val c-indigo">₱{{ number_format($project->current_budget, 0) }}</div>
                @if ($project->current_budget > 0)
                    <div class="stat-sub">{{ $project->budget_utilization }}% used</div>
                    <div class="mini-bar">
                        <div class="mini-fill" style="width:{{ min($project->budget_utilization, 100) }}%"></div>
                    </div>
                @endif
            </div>
            <div class="stat-card">
                <div class="stat-lbl">Expenses</div>
                <div class="stat-val c-red">₱{{ number_format($expenses->sum('amount'), 0) }}</div>
                <div class="stat-sub">{{ $expenses->count() }} {{ Str::plural('entry', $expenses->count()) }}</div>
            </div>
        </div>

        {{-- Tab shell --}}
        <div class="tab-shell" x-data="{ tab: 'add' }">

            <div class="tab-nav">
                <button class="tab-btn" :class="{ active: tab==='add' }" @click="tab='add'">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="16" />
                        <line x1="8" y1="12" x2="16" y2="12" />
                    </svg>
                    Add Transaction
                </button>
                <button class="tab-btn" :class="{ active: tab==='ledger' }" @click="tab='ledger'">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                    </svg>
                    Ledger
                </button>
                <button class="tab-btn" :class="{ active: tab==='expenses' }" @click="tab='expenses'">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round">
                        <line x1="18" y1="20" x2="18" y2="10" />
                        <line x1="12" y1="20" x2="12" y2="4" />
                        <line x1="6" y1="20" x2="6" y2="14" />
                    </svg>
                    Analysis
                </button>
            </div>

            {{-- ADD TRANSACTION FORM --}}
            <div x-show="tab==='add'">
                <form action="{{ route('projects.transactions.store', $project) }}" method="POST" class="add-form"
                    enctype="multipart/form-data">
                    @csrf

                    <div>
                        <label class="field-lbl">Type</label>
                        <div class="type-toggle">
                            <input type="radio" name="type" id="t-budget" value="budget_addition" checked>
                            <label for="t-budget">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    style="margin-right:4px;vertical-align:middle;">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                                </svg>
                                Budget Addition
                            </label>
                            <input type="radio" name="type" id="t-expense" value="expense">
                            <label for="t-expense">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    style="margin-right:4px;vertical-align:middle;">
                                    <polyline points="23 18 13.5 8.5 8.5 13.5 1 6" />
                                </svg>
                                Expense
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div>
                            <label class="field-lbl">Name / Item</label>
                            <input type="text" name="expense_name" required placeholder="What is this?"
                                class="field-in">
                        </div>
                        <div>
                            <label class="field-lbl">Amount</label>
                            <input type="text" inputmode="decimal" name="amount" required placeholder="0.00"
                                class="field-in"
                                oninput="this.value=this.value.replace(/[^0-9.]/g,'');this.setAttribute('data-raw',this.value)"
                                onblur="if(this.value)this.value=parseFloat(this.value.replace(/,/g,'')).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2})"
                                onfocus="this.value=this.getAttribute('data-raw')||this.value.replace(/,/g,'')">
                        </div>
                    </div>

                    <div class="form-row">
                        <div>
                            <label class="field-lbl">Category</label>
                            <div class="category-selector" x-data="categorySelector()" @click.away="showDropdown = false">
                                <input type="text" x-model="searchTerm" @focus="showDropdown = true"
                                    @input="filterCategories()" @keydown.enter.prevent="selectCategory(searchTerm)"
                                    placeholder="Type or select category..." class="field-in" autocomplete="off">

                                <input type="hidden" name="expense_category_search" x-model="selectedCategory">

                                <div x-show="showDropdown && filteredCategories.length > 0" class="category-dropdown"
                                    style="position: absolute; z-index: 1000; background: white; border: 1px solid var(--border); border-radius: var(--radius-sm); max-height: 200px; overflow-y: auto; width: calc(100% - 28px); margin-top: 4px;">
                                    <template x-for="cat in filteredCategories" :key="cat">
                                        <div @click="selectCategory(cat)" class="category-option"
                                            style="padding: 10px 14px; cursor: pointer; transition: background 0.2s;"
                                            @mouseenter="this.style.background='var(--indigo-light)'"
                                            @mouseleave="this.style.background='white'">
                                            <span x-text="cat"></span>
                                        </div>
                                    </template>
                                </div>

                                <div x-show="showDropdown && filteredCategories.length === 0 && searchTerm.length > 0"
                                    class="category-dropdown"
                                    style="position: absolute; z-index: 1000; background: white; border: 1px solid var(--border); border-radius: var(--radius-sm); width: calc(100% - 28px); margin-top: 4px;">
                                    <div class="category-option" @click="selectCategory(searchTerm)"
                                        style="padding: 10px 14px; cursor: pointer; color: var(--green);">
                                        ➕ Create "<span x-text="searchTerm"></span>"
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="field-lbl">Date</label>
                            <input type="date" name="transaction_date" required value="{{ date('Y-m-d') }}"
                                class="field-in">
                        </div>
                    </div>

                    <div class="form-row">
                        <div>
                            <label class="field-lbl">Client Name <span style="font-weight:400;color:var(--ink-4);">(for
                                    payments)</span></label>
                            <input type="text" name="client_name" placeholder="Client name" class="field-in">
                        </div>
                        <div>
                            <label class="field-lbl">Invoice / PO Ref</label>
                            <input type="text" name="invoice_ref" placeholder="INV-001" class="field-in">
                        </div>
                    </div>

                    <div>
                        <label class="field-lbl">Notes</label>
                        <textarea name="description" rows="2" placeholder="Anything else…" class="field-in"></textarea>
                    </div>

                    <div>
                        <label class="field-lbl">Proof / Receipt</label>
                        <input type="file" name="proof_image" accept="image/*" class="field-in"
                            style="padding:8px 14px;">
                    </div>

                    <button type="submit" class="btn-save">Save Transaction</button>
                </form>
            </div>

            {{-- ENHANCED LEDGER WITH COLLAPSIBLE CATEGORIES, SEARCH, PAGINATION --}}
            <div x-show="tab==='ledger'" class="ledger-wrap" x-data="ledgerComponent()" x-init="init()">

                {{-- Search and Filter Bar --}}
                <div class="ledger-filters">
                    <div class="filter-row">
                        <div style="flex: 1; min-width: 200px;">
                            <input type="text" x-model="searchTerm" @input="filterTransactions()"
                                placeholder="🔍 Search by name, category, or client..." class="field-in"
                                style="background: var(--white);">
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <select x-model="typeFilter" @change="filterTransactions()" class="field-in"
                                style="width: auto; min-width: 120px;">
                                <option value="all">All Types</option>
                                <option value="budget_addition">Budget Additions</option>
                                <option value="expense">Expenses</option>
                            </select>
                            <button @click="resetFilters()" class="btn-cancel" style="padding: 11px 16px;">Reset</button>
                        </div>
                    </div>

                    {{-- Report Export Buttons --}}
                    <div class="report-buttons">
                        <a href="{{ route('projects.report.excel', $project) }}" class="btn-report btn-excel">
                            📊 Export to Excel
                        </a>
                        <a href="{{ route('projects.report.pdf', $project) }}" class="btn-report btn-pdf">
                            📄 Export to PDF
                        </a>
                        <a href="{{ route('projects.report.word', $project) }}" class="btn-report btn-word">
                            📝 Export to Word
                        </a>
                    </div>
                </div>

                {{-- Transactions Display --}}
                <div class="ledger-categories">
                    @if ($transactions->isEmpty())
                        <div class="empty-ledger">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                                stroke="var(--indigo)" stroke-width="1.5" stroke-linecap="round"
                                style="margin:0 auto 10px;display:block;opacity:.35;">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            No transactions yet.
                        </div>
                    @else
                        {{-- Expense Categories Section --}}
                        <template x-for="(category, categoryName) in filteredCategories" :key="categoryName">
                            <div class="category-group" :data-category="categoryName">
                                <div class="category-header" @click="toggleCategory(categoryName)">
                                    <div class="category-title">
                                        <span class="toggle-icon"
                                            x-text="collapsedCategories.includes(categoryName) ? '▶' : '▼'"></span>
                                        <span class="category-name" x-text="categoryName"></span>
                                        <span class="category-count" x-text="`(${category.transactions.length})`"></span>
                                    </div>
                                    <div class="category-total" x-text="formatCurrency(category.total)"></div>
                                </div>

                                <div class="category-content" x-show="!collapsedCategories.includes(categoryName)"
                                    x-collapse>
                                    <div class="ledger-cards">
                                        <template x-for="transaction in getPaginatedTransactions(category.transactions)"
                                            :key="transaction.id">
                                            <div class="ledger-item">
                                                <div class="l-dot" :class="transaction.type"></div>
                                                <div class="l-body">
                                                    <div class="l-name"
                                                        x-text="transaction.expense_name || transaction.description || '—'">
                                                    </div>
                                                    <div class="l-meta">
                                                        <span x-text="formatDate(transaction.transaction_date)"></span>
                                                        <span x-text="transaction.category || '—'"></span>
                                                        <template x-if="transaction.client_name">
                                                            <span x-text="`Client: ${transaction.client_name}`"></span>
                                                        </template>
                                                    </div>
                                                </div>
                                                <div class="l-right">
                                                    <div class="l-amount" :class="transaction.type"
                                                        x-text="formatAmount(transaction.amount, transaction.type)"></div>
                                                    <div class="transaction-actions">
                                                        <template x-if="transaction.proof_image">
                                                            <button @click="viewImage(transaction)" class="btn-view-image"
                                                                title="View receipt">
                                                                📷
                                                            </button>
                                                        </template>
                                                        <span class="tbadge" :class="transaction.type"
                                                            x-text="getTypeLabel(transaction.type)"></span>
                                                    </div>
                                                </div>
                                                <form
                                                    :action="`/projects/{{ $project->id }}/transactions/${transaction.id}`"
                                                    method="POST" onsubmit="return confirm('Remove this transaction?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn-del">
                                                        <svg width="14" height="14" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="2">
                                                            <polyline points="3 6 5 6 21 6" />
                                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                                            <path d="M10 11v6" />
                                                            <path d="M14 11v6" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Budget Additions Section --}}
                        <template x-if="filteredBudgetAdditions.length > 0">
                            <div class="category-group" data-category="budget-additions">
                                <div class="category-header budget-header" @click="toggleCategory('budget-additions')">
                                    <div class="category-title">
                                        <span class="toggle-icon"
                                            x-text="collapsedCategories.includes('budget-additions') ? '▶' : '▼'"></span>
                                        <span class="category-name">💰 Budget Additions (Client Payments)</span>
                                        <span class="category-count"
                                            x-text="`(${filteredBudgetAdditions.length})`"></span>
                                    </div>
                                    <div class="category-total" style="color: var(--green);"
                                        x-text="formatCurrency(budgetAdditionsTotal)"></div>
                                </div>

                                <div class="category-content" x-show="!collapsedCategories.includes('budget-additions')"
                                    x-collapse>
                                    <div class="ledger-cards">
                                        <template x-for="transaction in getPaginatedBudgetAdditions()"
                                            :key="transaction.id">
                                            <div class="ledger-item">
                                                <div class="l-dot budget_addition"></div>
                                                <div class="l-body">
                                                    <div class="l-name"
                                                        x-text="transaction.expense_name || transaction.description || '—'">
                                                    </div>
                                                    <div class="l-meta">
                                                        <span x-text="formatDate(transaction.transaction_date)"></span>
                                                        <template x-if="transaction.client_name">
                                                            <span x-text="`Client: ${transaction.client_name}`"></span>
                                                        </template>
                                                        <template x-if="transaction.invoice_ref">
                                                            <span x-text="`Ref: ${transaction.invoice_ref}`"></span>
                                                        </template>
                                                    </div>
                                                </div>
                                                <div class="l-right">
                                                    <div class="l-amount budget_addition"
                                                        x-text="formatAmount(transaction.amount, 'budget_addition')"></div>
                                                    <div class="transaction-actions">
                                                        <template x-if="transaction.proof_image">
                                                            <button @click="viewImage(transaction)" class="btn-view-image"
                                                                title="View receipt">
                                                                📷
                                                            </button>
                                                        </template>
                                                        <span class="tbadge budget_addition">Budget Addition</span>
                                                    </div>
                                                </div>
                                                <form
                                                    :action="`/projects/{{ $project->id }}/transactions/${transaction.id}`"
                                                    method="POST" onsubmit="return confirm('Remove this transaction?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn-del">
                                                        <svg width="14" height="14" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="2">
                                                            <polyline points="3 6 5 6 21 6" />
                                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                                            <path d="M10 11v6" />
                                                            <path d="M14 11v6" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Pagination --}}
                        <div class="pagination-wrapper">
                            <div class="pagination-info">
                                Showing <span x-text="pagination.from"></span> to <span x-text="pagination.to"></span> of
                                <span x-text="pagination.total"></span> transactions
                            </div>
                            <div class="pagination-controls">
                                <button @click="previousPage" :disabled="pagination.current_page === 1"
                                    class="btn-cancel"
                                    :style="pagination.current_page === 1 ? 'opacity: 0.5; cursor: not-allowed;' : ''">
                                    ← Previous
                                </button>
                                <span
                                    style="padding: 8px 14px; background: var(--indigo-light); border-radius: var(--radius-sm); font-size: 13px; font-weight: 600;">
                                    Page <span x-text="pagination.current_page"></span> of <span
                                        x-text="pagination.last_page"></span>
                                </span>
                                <button @click="nextPage" :disabled="pagination.current_page === pagination.last_page"
                                    class="btn-cancel"
                                    :style="pagination.current_page === pagination.last_page ?
                                        'opacity: 0.5; cursor: not-allowed;' : ''">
                                    Next →
                                </button>
                            </div>
                            <div class="items-per-page" style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 13px; color: var(--ink-4);">Show:</span>
                                <select x-model="perPage" @change="changePerPage()" class="field-in"
                                    style="width: auto; padding: 6px 10px;">
                                    <option value="5">5 per page</option>
                                    <option value="10">10 per page</option>
                                    <option value="25">25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                </select>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ANALYSIS SECTION --}}
            <div x-show="tab==='expenses'" class="analysis-pad">
                <p class="section-title">Expenses by Category</p>
                @if ($categorySummary->count() > 0)
                    @foreach ($categorySummary as $catName => $total)
                        @php
                            $totalExpenses = $expenses->sum('amount');
                            $pct = $totalExpenses > 0 ? round(($total / $totalExpenses) * 100, 1) : 0;
                        @endphp
                        <div class="cat-item">
                            <div class="cat-left">
                                <div class="cat-name">{{ $catName }}</div>
                                <div class="cat-bar">
                                    <div class="cat-fill" style="width:{{ $pct }}%"></div>
                                </div>
                                <div class="cat-pct">{{ $pct }}%</div>
                            </div>
                            <div class="cat-amt">₱{{ number_format($total, 2) }}</div>
                        </div>
                    @endforeach
                    <div
                        style="display:flex;justify-content:space-between;font-size:13px;font-weight:700;padding:12px 12px 0;border-top:1px solid #f3f4f6;margin-top:8px;">
                        <span>Total Expenses</span>
                        <span style="color:var(--red);">₱{{ number_format($expenses->sum('amount'), 2) }}</span>
                    </div>
                @else
                    <p style="color:var(--ink-4);font-size:14px;padding:32px 0;text-align:center;">No expense data yet.</p>
                @endif

                <div class="analysis-divider"></div>

                <p class="section-title">Budget vs Actual</p>
                <div style="max-width: 220px; margin: 0 auto;">
                    <canvas id="budgetChart"></canvas>
                </div>

                <div class="budget-boxes">
                    <div class="bx">
                        <div class="bx-lbl">Total Budget</div>
                        <div class="bx-val">₱{{ number_format($project->budget + $budgetAdditions->sum('amount'), 0) }}
                        </div>
                    </div>
                    <div class="bx">
                        <div class="bx-lbl">Total Spent</div>
                        <div class="bx-val red">₱{{ number_format($expenses->sum('amount'), 0) }}</div>
                    </div>
                </div>
            </div>

        </div>{{-- /tab-shell --}}

        <div class="page-bottom"></div>
    </div>

    {{-- Edit Project Modal --}}
    @if(Auth::user()->isAdmin())
    <div id="editModal" class="modal-bg" onclick="if(event.target===this)this.classList.remove('open')">
        <div class="modal-box">
            <div class="modal-handle"></div>
            <p class="modal-title">Edit Project</p>
            <form action="{{ route('projects.update', $project) }}" method="POST"
                style="display:flex;flex-direction:column;gap:14px;">
                @csrf @method('PUT')
                <div>
                    <label class="field-lbl">Project Name</label>
                    <input type="text" name="name" value="{{ $project->name }}" required class="field-in">
                </div>
                <div>
                    <label class="field-lbl">Description</label>
                    <textarea name="description" rows="2" class="field-in">{{ $project->description }}</textarea>
                </div>
                <div>
                    <label class="field-lbl">Initial Budget</label>
                    <input type="number" step="0.01" name="budget" value="{{ $project->budget }}"
                        class="field-in">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel"
                        onclick="document.getElementById('editModal').classList.remove('open')">Cancel</button>
                    <button type="submit" class="btn-confirm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Image View Modal --}}
    <div id="imageModal" class="modal-bg" onclick="if(event.target===this)closeImageModal()">
        <div class="modal-box image-modal">
            <div class="modal-handle"></div>
            <div style="text-align: center;">
                <img id="modalImage" src="" alt="Receipt/Proof"
                    style="max-width: 100%; max-height: 70vh; border-radius: var(--radius-sm);">
            </div>
            <div class="modal-footer" style="justify-content: center;">
                <button type="button" class="btn-cancel" onclick="closeImageModal()">Close</button>
                <a id="downloadImageLink" href="#" download class="btn-confirm"
                    style="text-decoration: none; display: inline-block;">Download</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        // Chart initialization
        const bCtx = document.getElementById('budgetChart')?.getContext('2d');
        if (bCtx) {
            const totalBudget = {{ $project->budget + $budgetAdditions->sum('amount') }};
            const spent = {{ $expenses->sum('amount') }};
            const remaining = Math.max(totalBudget - spent, 0);

            new Chart(bCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Spent', 'Remaining'],
                    datasets: [{
                        data: [spent, remaining],
                        backgroundColor: ['#ef4444', '#e0e7ff'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '74%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 11
                                },
                                color: '#9ca3af',
                                boxWidth: 8,
                                padding: 16
                            }
                        }
                    }
                }
            });
        }

        function categorySelector() {
            return {
                categories: @json($expenseCategories->pluck('name')),
                searchTerm: '',
                selectedCategory: '',
                showDropdown: false,
                filteredCategories: [],

                init() {
                    this.filteredCategories = this.categories;
                },

                filterCategories() {
                    if (this.searchTerm === '') {
                        this.filteredCategories = this.categories;
                    } else {
                        this.filteredCategories = this.categories.filter(cat =>
                            cat.toLowerCase().includes(this.searchTerm.toLowerCase())
                        );
                    }
                    this.showDropdown = true;
                },

                selectCategory(category) {
                    this.selectedCategory = category;
                    this.searchTerm = category;
                    this.showDropdown = false;
                }
            };
        }
        // Alpine.js Ledger Component
        function ledgerComponent() {
            return {
                transactions: @json($transactions),
                searchTerm: '',
                typeFilter: 'all',
                perPage: 10,
                currentPage: 1,
                collapsedCategories: [],
                filteredTransactions: [],

                init() {
                    this.filterTransactions();
                    // Load collapsed state from localStorage
                    const saved = localStorage.getItem('collapsedCategories');
                    if (saved) {
                        this.collapsedCategories = JSON.parse(saved);
                    }
                },

                get filteredCategories() {
                    const expenses = this.filteredTransactions.filter(t => t.type === 'expense');
                    const grouped = {};
                    expenses.forEach(t => {
                        const category = t.category || 'Uncategorized';
                        if (!grouped[category]) {
                            grouped[category] = {
                                transactions: [],
                                total: 0
                            };
                        }
                        grouped[category].transactions.push(t);
                        grouped[category].total += parseFloat(t.amount);
                    });
                    return grouped;
                },

                get filteredBudgetAdditions() {
                    return this.filteredTransactions.filter(t => t.type === 'budget_addition');
                },

                get budgetAdditionsTotal() {
                    return this.filteredBudgetAdditions.reduce((sum, t) => sum + parseFloat(t.amount), 0);
                },

                get pagination() {
                    const start = (this.currentPage - 1) * this.perPage;
                    const end = start + this.perPage;
                    const paginatedItems = this.filteredTransactions.slice(start, end);

                    return {
                        current_page: this.currentPage,
                        last_page: Math.ceil(this.filteredTransactions.length / this.perPage) || 1,
                        per_page: this.perPage,
                        total: this.filteredTransactions.length,
                        from: this.filteredTransactions.length === 0 ? 0 : start + 1,
                        to: Math.min(end, this.filteredTransactions.length)
                    };
                },

                getPaginatedTransactions(transactions) {
                    const start = (this.currentPage - 1) * this.perPage;
                    const end = start + this.perPage;
                    // This is a simplified pagination - for better UX, you might want to paginate within categories
                    return transactions;
                },

                getPaginatedBudgetAdditions() {
                    const start = (this.currentPage - 1) * this.perPage;
                    const end = start + this.perPage;
                    return this.filteredBudgetAdditions;
                },

                filterTransactions() {
                    let filtered = [...this.transactions];

                    if (this.searchTerm) {
                        const term = this.searchTerm.toLowerCase();
                        filtered = filtered.filter(t =>
                            (t.expense_name && t.expense_name.toLowerCase().includes(term)) ||
                            (t.description && t.description.toLowerCase().includes(term)) ||
                            (t.category && t.category.toLowerCase().includes(term)) ||
                            (t.client_name && t.client_name.toLowerCase().includes(term))
                        );
                    }

                    if (this.typeFilter !== 'all') {
                        filtered = filtered.filter(t => t.type === this.typeFilter);
                    }

                    this.filteredTransactions = filtered;
                    this.currentPage = 1;
                },

                resetFilters() {
                    this.searchTerm = '';
                    this.typeFilter = 'all';
                    this.filterTransactions();
                },

                toggleCategory(categoryName) {
                    const index = this.collapsedCategories.indexOf(categoryName);
                    if (index === -1) {
                        this.collapsedCategories.push(categoryName);
                    } else {
                        this.collapsedCategories.splice(index, 1);
                    }
                    localStorage.setItem('collapsedCategories', JSON.stringify(this.collapsedCategories));
                },

                previousPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                    }
                },

                nextPage() {
                    if (this.currentPage < this.pagination.last_page) {
                        this.currentPage++;
                    }
                },

                changePerPage() {
                    this.currentPage = 1;
                },

                viewImage(transaction) {
                    if (transaction.proof_image) {
                        const imageUrl = `/storage/${transaction.proof_image}`;
                        const modal = document.getElementById('imageModal');
                        const img = document.getElementById('modalImage');
                        const downloadLink = document.getElementById('downloadImageLink');

                        img.src = imageUrl;
                        downloadLink.href = imageUrl;
                        modal.classList.add('open');
                    }
                },

                formatCurrency(amount) {
                    return '₱' + parseFloat(amount).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },

                formatAmount(amount, type) {
                    const formatted = this.formatCurrency(amount);
                    return type === 'expense' ? `-${formatted}` : `+${formatted}`;
                },

                formatDate(date) {
                    if (!date) return '';
                    return new Date(date).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                },

                getTypeLabel(type) {
                    return type === 'budget_addition' ? 'Budget Addition' : 'Expense';
                }
            };
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.remove('open');
            document.getElementById('modalImage').src = '';
        }
    </script>
@endsection
