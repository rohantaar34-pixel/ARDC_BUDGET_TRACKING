{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.app')

@section('content')
    <style>
        .login-wrap {
            min-height: calc(100vh - 80px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
        }

        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, .1);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }

        .login-card-head {
            background: #BE0000;
            padding: 32px 36px 28px;
            text-align: center;
        }

        .login-card-head h2 {
            color: #fff;
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
            margin: 0 0 4px;
        }

        .login-card-head p {
            color: rgba(255, 255, 255, .65);
            font-size: .78rem;
            letter-spacing: .06em;
            text-transform: uppercase;
            margin: 0;
        }

        .login-card-body {
            padding: 32px 36px 36px;
        }

        .alert-box {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: .84rem;
            margin-bottom: 22px;
        }

        .alert-error {
            background: #fff0f0;
            border: 1px solid #fcc;
            color: #8B0000;
        }

        .alert-banned {
            background: #111;
            border: 1px solid #333;
            color: #ff6b6b;
        }

        .f-group {
            margin-bottom: 18px;
        }

        .f-group label {
            display: block;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #555;
            margin-bottom: 6px;
        }

        .f-group input {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            font-size: .95rem;
            color: #111;
            background: #fafafa;
            outline: none;
            font-family: 'Montserrat', sans-serif;
            transition: border-color .15s, box-shadow .15s;
        }

        .f-group input:focus {
            border-color: #BE0000;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(190, 0, 0, .1);
        }

        .f-group input.is-err {
            border-color: #BE0000;
            background: #fff8f8;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 26px;
        }

        .remember-row input[type="checkbox"] {
            accent-color: #BE0000;
            width: 15px;
            height: 15px;
            cursor: pointer;
        }

        .remember-row label {
            font-size: .85rem;
            color: #666;
            cursor: pointer;
            font-weight: 400;
            text-transform: none;
            letter-spacing: 0;
        }

        .btn-signin {
            width: 100%;
            padding: 13px;
            background: #BE0000;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: .93rem;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            box-shadow: 0 4px 16px rgba(190, 0, 0, .28);
            transition: background .15s, box-shadow .15s, transform .1s;
        }

        .btn-signin:hover {
            background: #9a0000;
            box-shadow: 0 6px 20px rgba(190, 0, 0, .38);
        }

        .btn-signin:active {
            transform: scale(.98);
        }

        .login-footnote {
            margin-top: 24px;
            font-size: .73rem;
            color: #bbb;
            text-align: center;
            line-height: 1.8;
        }
    </style>

    <div class="login-wrap">
        <div class="login-card">

            <div class="login-card-head">
                <h2>Sign In</h2>
                <p>Restricted — authorised personnel only</p>
            </div>

            <div class="login-card-body">

                @if ($errors->has('email') && str_contains($errors->first('email'), 'banned'))
                    <div class="alert-box alert-banned">
                        <span>🚫</span>
                        <span>{{ $errors->first('email') }}</span>
                    </div>
                @elseif ($errors->any())
                    <div class="alert-box alert-error">
                        <span>⚠</span>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf

                    <div class="f-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            placeholder="you@ardc.com" autocomplete="email" autofocus
                            class="{{ $errors->has('email') ? 'is-err' : '' }}">
                    </div>

                    <div class="f-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="••••••••"
                            autocomplete="current-password" class="{{ $errors->has('password') ? 'is-err' : '' }}">
                    </div>

                    <div class="remember-row">
                        <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember">Keep me signed in</label>
                    </div>

                    <button type="submit" class="btn-signin">Sign In</button>
                </form>

                <div class="login-footnote">
                    🔒 No self-registration. Contact your administrator for access.
                </div>

            </div>
        </div>
    </div>
@endsection
