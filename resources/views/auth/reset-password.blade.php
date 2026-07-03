@extends('layouts.app')

@section('content')
    @include('auth.partials.card-styles')

    <div class="auth-wrap">
        <div class="auth-card">
            <div class="auth-card-head">
                <h2>Create New Password</h2>
                <p>Secure your account</p>
            </div>

            <div class="auth-card-body">
                <p class="auth-caption">
                    Choose a new password for your account. This also clears any active login lockout history.
                </p>

                @if ($errors->any())
                    <div class="alert-box alert-error">
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="f-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $email) }}"
                            placeholder="you@ardc.com" autocomplete="email" autofocus
                            class="{{ $errors->has('email') ? 'is-err' : '' }}">
                    </div>

                    <div class="f-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" placeholder="Create a new password"
                            autocomplete="new-password" class="{{ $errors->has('password') ? 'is-err' : '' }}">
                    </div>

                    <div class="f-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            placeholder="Repeat the new password" autocomplete="new-password"
                            class="{{ $errors->has('password') ? 'is-err' : '' }}">
                    </div>

                    <button type="submit" class="btn-primary">Reset Password</button>
                </form>

                <div class="auth-footnote">
                    <a href="{{ route('login') }}" class="auth-link">Back to sign in</a>
                </div>
            </div>
        </div>
    </div>
@endsection
