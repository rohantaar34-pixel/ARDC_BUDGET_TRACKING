{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="true">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>ARDC Project Budget Tracking</title>
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
            --ardc-red: #D60000;
            --ardc-red-light: #FF3333;
            --ardc-red-dark: #990000;
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
    </style>
</head>

<body>
    <div class="min-h-screen flex flex-col">

        <!-- Navigation Bar -->
        <nav class="bg-white border-b-4 border-red-600 sticky top-0 z-40 shadow-sm">
            <div class="w-full px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between">

                <a href="{{ Auth::check() && Auth::user()->isEmployee() ? route('monitoring.submit') : route('projects.index') }}"
                    class="flex items-center gap-2 sm:gap-3 flex-shrink-0 hover:opacity-80 transition-opacity">
                    <img src="{{ asset('images/logo.jpg') }}" alt="ARDC Logo"
                        class="h-10 sm:h-12 w-auto object-contain">
                    <div class="hidden sm:flex flex-col">
                        <span class="text-sm font-black text-slate-900">ARDC</span>
                        <p class="text-xs text-red-600 font-bold leading-tight">Budget Tracking</p>
                    </div>
                </a>

                @auth
                    <div style="display:flex; align-items:center; gap:12px;">
                        <span style="font-size:.8rem; color:#888; font-weight:600;">{{ Auth::user()->name }}</span>
                        
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('settings.projects.index') }}" class="btn-nav-logout" style="background: rgba(99, 102, 241, 0.15); border-color: rgba(99, 102, 241, 0.3); color: #4f46e5; text-decoration: none;">
                                Settings
                            </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}" class="nav-logout-form">
                            @csrf
                            <button type="submit" class="btn-nav-logout" style="background:#BE0000; border-color:#BE0000;">
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
                        class="mb-6 sm:mb-8 p-4 px-4 sm:px-6 bg-gradient-to-r from-red-50 to-red-100 border-2 border-red-600 text-red-700 rounded-xl flex items-center gap-3 animate-fade-in">
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
