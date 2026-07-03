@extends('layouts.app')

@section('content')
    @include('auth.partials.card-styles')

    <div class="auth-wrap">
        <div class="auth-card">
            <div class="auth-card-head">
                <h2>Reset Password</h2>
                <p>Recover account access</p>
            </div>

            <div class="auth-card-body">
                <p class="auth-caption">
                    Enter your account email address and the system will send a reset link if that account exists.
                </p>

                @if (session('status'))
                    <div class="alert-box alert-success">
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert-box alert-error">
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="f-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            placeholder="you@ardc.com" autocomplete="email" autofocus
                            class="{{ $errors->has('email') ? 'is-err' : '' }}">
                    </div>

                    <button type="submit" class="btn-primary">Email Reset Link</button>
                </form>

                <div class="auth-footnote">
                    <a href="{{ route('login') }}" class="auth-link">Back to sign in</a>
                </div>
            </div>
        </div>
    </div>
@endsection
