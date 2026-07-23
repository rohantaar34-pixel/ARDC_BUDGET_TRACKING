@extends('layouts.app')

@php
    $moduleCatalog = \App\Models\User::moduleCatalog();
    $moduleLabels = collect($user->resolvedModulePermissions())
        ->map(fn ($module) => $moduleCatalog[$module]['label'] ?? Str::headline($module))
        ->unique()
        ->values();
@endphp

@section('content')
<style>
    :root {
        --profile-ink: #111827;
        --profile-muted: #64748b;
        --profile-line: #e2e8f0;
        --profile-primary: #0f766e;
        --profile-soft: #f8fafc;
    }

    .profile-page {
        width: min(1050px, 100%);
        margin: 0 auto;
        padding: 4px 0 36px;
        color: var(--profile-ink);
    }

    .profile-topbar {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 22px;
    }

    .profile-kicker {
        margin-bottom: 6px;
        color: var(--profile-primary);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .profile-title { margin: 0; font-size: clamp(27px, 5vw, 38px); line-height: 1.1; letter-spacing: -.035em; }
    .profile-subtitle { margin: 8px 0 0; color: var(--profile-muted); font-size: 13px; line-height: 1.6; }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        flex: 0 0 auto;
        padding: 9px 13px;
        border: 1px solid #dbe2ea;
        border-radius: 10px;
        background: #fff;
        color: #475569;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: minmax(260px, 330px) minmax(0, 1fr);
        gap: 18px;
        align-items: start;
    }

    .profile-card {
        border: 1px solid var(--profile-line);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .05);
    }

    .identity-card { padding: 22px; }
    .avatar {
        display: grid;
        width: 70px;
        height: 70px;
        margin-bottom: 16px;
        place-items: center;
        border-radius: 20px;
        background: linear-gradient(145deg, #e0e7ff, #c7d2fe);
        color: #1d4ed8;
        font-size: 28px;
        font-weight: 900;
    }

    .identity-name { margin: 0; font-size: 20px; overflow-wrap: anywhere; }
    .identity-role { margin: 5px 0 0; color: var(--profile-primary); font-size: 12px; font-weight: 850; }
    .identity-position { margin: 3px 0 18px; color: var(--profile-muted); font-size: 12px; }

    .detail-list { display: grid; gap: 10px; padding-top: 16px; border-top: 1px solid var(--profile-line); }
    .detail-item { display: grid; gap: 3px; }
    .detail-label { color: #94a3b8; font-size: 9px; font-weight: 900; letter-spacing: .07em; text-transform: uppercase; }
    .detail-value { color: #334155; font-size: 12px; font-weight: 700; overflow-wrap: anywhere; }

    .module-section { margin-top: 18px; }
    .module-heading { margin: 0 0 9px; color: #475569; font-size: 11px; font-weight: 900; }
    .module-chips { display: flex; flex-wrap: wrap; gap: 6px; }
    .module-chip { padding: 5px 8px; border-radius: 8px; background: #ecfdf8; color: #0f766e; font-size: 9px; font-weight: 800; }

    .security-head { padding: 22px 24px 17px; border-bottom: 1px solid var(--profile-line); }
    .security-head h2 { margin: 0; font-size: 21px; }
    .security-head p { margin: 6px 0 0; color: var(--profile-muted); font-size: 12px; line-height: 1.55; }
    .password-form { padding: 21px 24px 24px; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .field { display: grid; gap: 7px; min-width: 0; }
    .field.full { grid-column: 1 / -1; }
    .field label { color: #334155; font-size: 12px; font-weight: 850; }

    .input-shell { position: relative; }
    .field-input {
        width: 100%;
        min-height: 47px;
        padding: 11px 67px 11px 13px;
        border: 1px solid #cbd5e1;
        border-radius: 11px;
        outline: none;
        background: #fff;
        color: var(--profile-ink);
        font: inherit;
        font-size: 14px;
    }

    .field-input:focus { border-color: var(--profile-primary); box-shadow: 0 0 0 3px rgba(15, 118, 110, .1); }
    .field-input.is-invalid { border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220, 38, 38, .08); }
    .toggle-password { position: absolute; top: 50%; right: 8px; padding: 7px 9px; border: 0; border-radius: 8px; background: #f1f5f9; color: #475569; font-size: 10px; font-weight: 850; cursor: pointer; transform: translateY(-50%); }
    .field-error { min-height: 16px; color: #b91c1c; font-size: 10px; font-weight: 750; }
    .field-hint { color: var(--profile-muted); font-size: 10px; line-height: 1.5; }

    .strength-box { display: none; margin-top: 2px; padding: 12px; border-radius: 11px; background: var(--profile-soft); }
    .strength-box.visible { display: block; }
    .strength-row { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 8px; color: #475569; font-size: 10px; font-weight: 800; }
    .strength-track { height: 7px; overflow: hidden; border-radius: 999px; background: #e2e8f0; }
    .strength-fill { width: 0; height: 100%; border-radius: inherit; background: #dc2626; transition: width .2s ease, background .2s ease; }
    .strength-fill.fair { background: #d97706; }
    .strength-fill.good { background: #0284c7; }
    .strength-fill.strong { background: #16a34a; }

    .rules { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 7px 12px; margin: 11px 0 0; padding: 0; list-style: none; }
    .rule { display: flex; align-items: center; gap: 6px; color: #64748b; font-size: 9px; }
    .rule::before { content: ''; width: 7px; height: 7px; flex: 0 0 auto; border-radius: 50%; background: #cbd5e1; }
    .rule.valid { color: #166534; }
    .rule.valid::before { background: #22c55e; }

    .security-note { display: flex; gap: 10px; margin-top: 18px; padding: 13px; border: 1px solid #dbeafe; border-radius: 11px; background: #eff6ff; color: #1e40af; font-size: 10px; line-height: 1.55; }
    .form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
    .btn-save { min-height: 44px; padding: 10px 18px; border: 0; border-radius: 11px; background: var(--profile-primary); color: #fff; font: inherit; font-size: 12px; font-weight: 900; cursor: pointer; }

    @media (max-width: 800px) {
        .profile-grid { grid-template-columns: 1fr; }
        .identity-card { display: grid; grid-template-columns: auto 1fr; gap: 0 16px; }
        .avatar { grid-row: span 2; margin: 0; }
        .detail-list, .module-section { grid-column: 1 / -1; }
    }

    @media (max-width: 620px) {
        .profile-topbar { display: block; }
        .back-link { margin-top: 15px; }
        .identity-card { display: block; padding: 18px; }
        .avatar { margin-bottom: 14px; }
        .form-grid, .rules { grid-template-columns: 1fr; }
        .field.full { grid-column: auto; }
        .security-head { padding: 18px; }
        .password-form { padding: 18px; }
        .form-actions .btn-save { width: 100%; }
    }
</style>

<div class="profile-page">
    <header class="profile-topbar">
        <div>
            <div class="profile-kicker">My Account</div>
            <h1 class="profile-title">Profile and security</h1>
            <p class="profile-subtitle">Review your account access and maintain your own sign-in password.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="back-link">
            <span aria-hidden="true">&larr;</span> Dashboard
        </a>
    </header>

    <div class="profile-grid">
        <aside class="profile-card identity-card">
            <div class="avatar">{{ Str::upper(Str::substr($user->name, 0, 1)) }}</div>
            <div>
                <h2 class="identity-name">{{ $user->name }}</h2>
                <p class="identity-role">{{ $user->displayRole() }}</p>
                <p class="identity-position">{{ $user->displayPosition() }}</p>
            </div>

            <div class="detail-list">
                <div class="detail-item"><span class="detail-label">Email</span><span class="detail-value">{{ $user->email }}</span></div>
                <div class="detail-item"><span class="detail-label">Account ID</span><span class="detail-value">#{{ $user->id }}</span></div>
                <div class="detail-item"><span class="detail-label">Member Since</span><span class="detail-value">{{ $user->created_at?->format('F d, Y') ?? 'Not available' }}</span></div>
            </div>

            <div class="module-section">
                <h3 class="module-heading">Assigned Modules</h3>
                <div class="module-chips">
                    @forelse($moduleLabels as $label)
                        <span class="module-chip">{{ $label }}</span>
                    @empty
                        <span class="module-chip">No modules assigned</span>
                    @endforelse
                </div>
            </div>
        </aside>

        <section class="profile-card">
            <div class="security-head">
                <h2>Change Password</h2>
                <p>Enter your existing password first, then create a strong password you have not used for this account.</p>
            </div>

            <form class="password-form" id="passwordForm" action="{{ route('profile.password.update') }}" method="POST" novalidate>
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="field full">
                        <label for="currentPassword">Current Password *</label>
                        <div class="input-shell">
                            <input class="field-input @error('current_password') is-invalid @enderror" id="currentPassword" name="current_password" type="password" autocomplete="current-password" required>
                            <button type="button" class="toggle-password" data-password-toggle="currentPassword">Show</button>
                        </div>
                        <div class="field-error" id="currentPasswordError">@error('current_password'){{ $message }}@enderror</div>
                    </div>

                    <div class="field">
                        <label for="newPassword">New Password *</label>
                        <div class="input-shell">
                            <input class="field-input @error('password') is-invalid @enderror" id="newPassword" name="password" type="password" autocomplete="new-password" required>
                            <button type="button" class="toggle-password" data-password-toggle="newPassword">Show</button>
                        </div>
                        <div class="field-error" id="newPasswordError">@error('password'){{ $message }}@enderror</div>
                    </div>

                    <div class="field">
                        <label for="passwordConfirmation">Confirm New Password *</label>
                        <div class="input-shell">
                            <input class="field-input" id="passwordConfirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                            <button type="button" class="toggle-password" data-password-toggle="passwordConfirmation">Show</button>
                        </div>
                        <div class="field-error" id="passwordConfirmationError"></div>
                    </div>

                    <div class="field full">
                        <div class="strength-box" id="strengthBox">
                            <div class="strength-row"><span id="strengthLabel">Too weak</span><span id="strengthMeta">0 of 5 checks</span></div>
                            <div class="strength-track"><div class="strength-fill" id="strengthFill"></div></div>
                            <ul class="rules">
                                <li class="rule" data-rule="length">At least 8 characters</li>
                                <li class="rule" data-rule="lower">One lowercase letter</li>
                                <li class="rule" data-rule="upper">One uppercase letter</li>
                                <li class="rule" data-rule="number">One number</li>
                                <li class="rule" data-rule="symbol">One symbol</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="security-note">
                    <strong>Security:</strong>
                    <span>Your current password is always verified. Administrators cannot view your password, only reset it.</span>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Update Password</button>
                </div>
            </form>
        </section>
    </div>
</div>

<script>
    const currentPassword = document.getElementById('currentPassword');
    const newPassword = document.getElementById('newPassword');
    const passwordConfirmation = document.getElementById('passwordConfirmation');

    function passwordScore(value) {
        const checks = {
            length: value.length >= 8,
            lower: /[a-z]/.test(value),
            upper: /[A-Z]/.test(value),
            number: /\d/.test(value),
            symbol: /[^A-Za-z0-9]/.test(value),
        };
        return { checks, passed: Object.values(checks).filter(Boolean).length };
    }

    function updateStrength() {
        const value = newPassword.value;
        const box = document.getElementById('strengthBox');
        const fill = document.getElementById('strengthFill');
        const label = document.getElementById('strengthLabel');
        const meta = document.getElementById('strengthMeta');

        box.classList.toggle('visible', value.length > 0);
        const result = passwordScore(value);
        const labels = ['Too weak', 'Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        const tones = ['', '', '', 'fair', 'good', 'strong'];

        fill.style.width = (result.passed * 20) + '%';
        fill.className = 'strength-fill ' + tones[result.passed];
        label.textContent = labels[result.passed];
        meta.textContent = result.passed + ' of 5 checks';

        document.querySelectorAll('[data-rule]').forEach(rule => {
            rule.classList.toggle('valid', !!result.checks[rule.dataset.rule]);
        });
    }

    function clearError(input, error) {
        input.classList.remove('is-invalid');
        document.getElementById(error).textContent = '';
    }

    function setError(input, error, message) {
        input.classList.add('is-invalid');
        document.getElementById(error).textContent = message;
    }

    document.querySelectorAll('[data-password-toggle]').forEach(button => {
        button.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.passwordToggle);
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            this.textContent = showing ? 'Show' : 'Hide';
        });
    });

    newPassword.addEventListener('input', function () {
        clearError(newPassword, 'newPasswordError');
        updateStrength();
        if (passwordConfirmation.value) passwordConfirmation.dispatchEvent(new Event('input'));
    });

    passwordConfirmation.addEventListener('input', function () {
        clearError(passwordConfirmation, 'passwordConfirmationError');
        if (this.value && this.value !== newPassword.value) {
            setError(passwordConfirmation, 'passwordConfirmationError', 'Password confirmation does not match.');
        }
    });

    currentPassword.addEventListener('input', () => clearError(currentPassword, 'currentPasswordError'));

    document.getElementById('passwordForm').addEventListener('submit', function (event) {
        let valid = true;
        clearError(currentPassword, 'currentPasswordError');
        clearError(newPassword, 'newPasswordError');
        clearError(passwordConfirmation, 'passwordConfirmationError');

        if (!currentPassword.value) {
            setError(currentPassword, 'currentPasswordError', 'Current password is required.');
            valid = false;
        }

        if (passwordScore(newPassword.value).passed !== 5) {
            setError(newPassword, 'newPasswordError', 'Use 8+ characters with uppercase, lowercase, number, and symbol.');
            valid = false;
        } else if (newPassword.value === currentPassword.value) {
            setError(newPassword, 'newPasswordError', 'The new password must be different from your current password.');
            valid = false;
        }

        if (!passwordConfirmation.value || passwordConfirmation.value !== newPassword.value) {
            setError(passwordConfirmation, 'passwordConfirmationError', 'Password confirmation does not match.');
            valid = false;
        }

        if (!valid) event.preventDefault();
    });
</script>
@endsection
