{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion — RH Manager</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=DM+Mono:wght@400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <style>
    .auth-page {
      min-height: 100vh;
      background: var(--ink);
      display: grid;
      grid-template-columns: 1fr 440px;
    }
    .auth-left {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 48px;
      position: relative;
      overflow: hidden;
    }
    .auth-left::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        repeating-linear-gradient(0deg, rgba(255,255,255,.02) 0, rgba(255,255,255,.02) 1px, transparent 1px, transparent 40px),
        repeating-linear-gradient(90deg, rgba(255,255,255,.02) 0, rgba(255,255,255,.02) 1px, transparent 1px, transparent 40px);
      pointer-events: none;
    }
    .auth-brand { position: relative; z-index: 1; }
    .auth-brand-name { font-size: 20px; font-weight: 700; color: white; letter-spacing: -.4px; }
    .auth-brand-sub { font-size: 13px; color: rgba(255,255,255,.4); margin-top: 3px; }
    .auth-hero { position: relative; z-index: 1; }
    .auth-hero-title {
      font-size: 38px;
      font-weight: 700;
      letter-spacing: -1.5px;
      color: white;
      line-height: 1.15;
      margin-bottom: 16px;
    }
    .auth-hero-sub { font-size: 15px; color: rgba(255,255,255,.5); max-width: 380px; line-height: 1.6; }
    .auth-stats { display: flex; gap: 32px; position: relative; z-index: 1; }
    .auth-stat-val { font-size: 24px; font-weight: 700; color: white; font-family: var(--mono); letter-spacing: -1px; }
    .auth-stat-lbl { font-size: 11px; color: rgba(255,255,255,.4); margin-top: 2px; }

    .auth-right {
      background: var(--surface);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 48px 40px;
    }
    .auth-form { width: 100%; }
    .auth-form-title { font-size: 22px; font-weight: 700; letter-spacing: -.4px; margin-bottom: 6px; }
    .auth-form-sub { font-size: 13px; color: var(--ink3); margin-bottom: 28px; }

    @media (max-width: 768px) {
      .auth-page { grid-template-columns: 1fr; }
      .auth-left { display: none; }
      .auth-right { min-height: 100vh; }
    }
  </style>
</head>
<body>

<div class="auth-page">
  <div class="auth-left">
    <div class="auth-brand">
      <div class="auth-brand-name">RH Manager</div>
      <div class="auth-brand-sub">Système de gestion du personnel</div>
    </div>
    <div class="auth-hero">
      <div class="auth-hero-title">Gérez votre<br>équipe avec<br>précision.</div>
      <div class="auth-hero-sub">Présences, salaires, documents et biométrie — tout centralisé dans un seul espace de travail.</div>
    </div>
    <div class="auth-stats">
      <div>
        <div class="auth-stat-val">100%</div>
        <div class="auth-stat-lbl">Automatisé</div>
      </div>
      <div>
        <div class="auth-stat-val">0</div>
        <div class="auth-stat-lbl">Erreurs manuelles</div>
      </div>
      <div>
        <div class="auth-stat-val">PDF</div>
        <div class="auth-stat-lbl">Fiches instant.</div>
      </div>
    </div>
  </div>

  <div class="auth-right">
    <div class="auth-form">
      <div class="auth-form-title">Connexion administrateur</div>
      <div class="auth-form-sub">Accès réservé à l'administrateur système.</div>

      @if($errors->any())
        <div class="alert alert-danger mb-4">
          <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="8" cy="8" r="6.5"/><path d="M8 5v3M8 11h.01"/>
          </svg>
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
          <label class="form-label" for="email">Adresse email</label>
          <input
            id="email"
            type="email"
            name="email"
            class="form-control"
            value="{{ old('email') }}"
            placeholder="admin@entreprise.com"
            required
            autofocus
          >
        </div>

        <div class="form-group">
          <label class="form-label" for="password">
            Mot de passe
            <a href="{{ route('password.request') }}" style="float:right;font-weight:400;color:var(--ink3);font-size:11.5px">Oublié ?</a>
          </label>
          <input
            id="password"
            type="password"
            name="password"
            class="form-control"
            placeholder="••••••••••"
            required
          >
        </div>

        <div style="display:flex;align-items:center;gap:8px;margin-bottom:22px">
          <input type="checkbox" id="remember" name="remember" style="width:14px;height:14px;accent-color:var(--ink)">
          <label for="remember" style="font-size:12.5px;color:var(--ink2);cursor:pointer">Se souvenir de moi</label>
        </div>

        <button type="submit" class="btn btn-primary w-full" style="padding:10px;font-size:14px;justify-content:center">
          Se connecter
          <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 8h10M9 4l4 4-4 4"/>
          </svg>
        </button>
      </form>

      <div style="margin-top:28px;padding-top:20px;border-top:1px solid var(--border);text-align:center">
        <a href="{{ route('pointage.index') }}" class="btn" style="width:100%;justify-content:center;gap:8px">
          <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M8 2C5.8 2 4 3.8 4 6c0 3 4 8 4 8s4-5 4-8c0-2.2-1.8-4-4-4z"/>
            <circle cx="8" cy="6" r="1.5"/>
          </svg>
          Interface de pointage public
        </a>
      </div>
    </div>
  </div>
</div>

</body>
</html>
