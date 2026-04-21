{{-- resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tableau de bord') — RH Manager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>

<body>

    <div class="app">

        {{-- ── Sidebar ──────────────────────────────────────── --}}
        <aside class="sidebar">
            <div class="sidebar-logo">
                <div class="logo-mark">
                    <div class="logo-icon">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <circle cx="8" cy="5" r="2.5" />
                            <path d="M2.5 14c0-3 2.5-5 5.5-5s5.5 2 5.5 5" />
                        </svg>
                    </div>
                    <div>
                        <div class="logo-text">RH Manager</div>
                        <div class="logo-sub">Gestion du personnel</div>
                    </div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-group-label">Principal</div>

                <a href="{{ route('admin.dashboard') }}"
                    class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg class="icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <rect x="1.5" y="1.5" width="5.5" height="5.5" rx="1.2" />
                        <rect x="9" y="1.5" width="5.5" height="5.5" rx="1.2" />
                        <rect x="1.5" y="9" width="5.5" height="5.5" rx="1.2" />
                        <rect x="9" y="9" width="5.5" height="5.5" rx="1.2" />
                    </svg>
                    Tableau de bord
                </a>

                <div class="nav-group-label">Ressources humaines</div>

                <a href="{{ route('admin.employes.index') }}"
                    class="nav-link {{ request()->routeIs('admin.employes*') ? 'active' : '' }}">
                    <svg class="icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <circle cx="6" cy="5" r="2.5" />
                        <path d="M1 14c0-2.8 2.2-5 5-5s5 2.2 5 5" />
                        <path d="M12 7.5c1.5.5 2.5 1.8 2.5 3.5" />
                        <circle cx="12" cy="4" r="1.8" />
                    </svg>
                    Employés
                </a>

                <a href="{{ route('admin.contrats.index') }}"
                    class="nav-link {{ request()->routeIs('admin.contrats*') ? 'active' : '' }}">
                    <svg class="icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M9.5 1.5H3a1 1 0 00-1 1v11a1 1 0 001 1h10a1 1 0 001-1V5.5z" />
                        <path d="M9 1.5V6h4.5M5 9h6M5 11.5h4" />
                    </svg>
                    Contrats
                </a>

                <a href="{{ route('admin.presences.index') }}"
                    class="nav-link {{ request()->routeIs('admin.presences*') ? 'active' : '' }}">
                    <svg class="icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <circle cx="8" cy="8" r="6.2" />
                        <path d="M8 5v3.5l2 1.5" />
                    </svg>
                    Présences
                </a>

                <a href="{{ route('admin.absences.index') }}"
                    class="nav-link {{ request()->routeIs('admin.absences*') ? 'active' : '' }}">
                    <svg class="icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <rect x="1.5" y="3" width="13" height="11" rx="1.5" />
                        <path d="M5 1.5V4M11 1.5V4M1.5 7h13" />
                        <path d="M6 10l1.5 1.5L10 9" />
                    </svg>
                    Absences & Sanctions
                </a>

                <a href="{{ route('admin.conges.index') }}"
                    class="nav-link {{ request()->routeIs('admin.conges*') ? 'active' : '' }}">
                    <svg class="icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M8 2.5c3 0 5 2 5 5a7 7 0 01-10 6.3" />
                        <path d="M3 3l10 10M3 13l2-2M13 3l-2 2" />
                    </svg>
                    Congés & Fériés
                </a>

                <div class="nav-group-label">Finance</div>

                <a href="{{ route('admin.salaires.index') }}"
                    class="nav-link {{ request()->routeIs('admin.salaires*') ? 'active' : '' }}">
                    <svg class="icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M8 1v14M5 4.5h4.5a2 2 0 010 4H5h5a2.5 2.5 0 010 5H4" />
                    </svg>
                    Salaires
                </a>

                <a href="{{ route('admin.pdf.fiches-collectives') }}"
                    class="nav-link {{ request()->routeIs('admin.pdf*') ? 'active' : '' }}">
                    <svg class="icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"
                        stroke-width="1.6">
                        <path d="M9.5 1.5H3a1 1 0 00-1 1v11a1 1 0 001 1h10a1 1 0 001-1V5.5z" />
                        <path d="M9 1.5V6h4.5M5 9h6M5 11.5h4" />
                    </svg>
                    Fiches de paie
                </a>

                <div class="nav-group-label">Outils</div>

                <a href="{{ route('admin.statistiques.index') }}"
                    class="nav-link {{ request()->routeIs('admin.statistiques*') ? 'active' : '' }}">
                    <svg class="icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"
                        stroke-width="1.6">
                        <path d="M1.5 13L5 8.5l3 2.5 3.5-5 3 3" />
                        <path d="M1 15h14" />
                    </svg>
                    Statistiques
                </a>

                <a href="{{ route('admin.parametres.index') }}"
                    class="nav-link {{ request()->routeIs('admin.parametres*') ? 'active' : '' }}">
                    <svg class="icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"
                        stroke-width="1.6">
                        <circle cx="8" cy="8" r="2" />
                        <path
                            d="M8 1.5v1M8 13.5v1M1.5 8h1M13.5 8h1M3.4 3.4l.7.7M11.9 11.9l.7.7M12.6 3.4l-.7.7M4.1 11.9l-.7.7" />
                    </svg>
                    Paramètres
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="user-dot"></div>
                <span>{{ auth()->user()->name ?? 'Administrateur' }}</span>
                <a href="{{ route('logout') }}"
                    onclick="event.preventDefault();document.getElementById('logout-form').submit();"
                    style="margin-left:auto;color:var(--ink3);font-size:11px">Déco.</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            </div>
        </aside>

        {{-- ── Main ─────────────────────────────────────────── --}}
        <div class="main">
            <header class="topbar">
                <div class="topbar-left">
                    <div>
                        <div class="topbar-title">@yield('page-title', 'Tableau de bord')</div>
                        <div class="topbar-sub">@yield('page-sub', '')</div>
                    </div>
                </div>
                <div class="topbar-right">
                    @yield('topbar-actions')
                </div>
            </header>

            <main class="page-content">
                @if (session('success'))
                    <div class="alert alert-success mb-4">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="8" cy="8" r="6.5" />
                            <path d="M5 8l2 2 4-4" />
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger mb-4">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="8" cy="8" r="6.5" />
                            <path d="M8 5v3.5M8 11h.01" />
                        </svg>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
    <script>
        // Close modals on overlay click
        document.querySelectorAll('.modal-overlay').forEach(o => {
            o.addEventListener('click', e => {
                if (e.target === o) o.style.display = 'none';
            });
        });
    </script>
</body>

</html>
