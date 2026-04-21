{{-- resources/views/auth/passwords/reset.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nouveau mot de passe — RH Manager</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body style="min-height:100vh;background:var(--ink);display:flex;align-items:center;justify-content:center;padding:24px">
  <div style="background:var(--surface);border-radius:var(--rxl);padding:40px 36px;width:420px;box-shadow:var(--sh3)">
    <div style="font-size:22px;font-weight:700;letter-spacing:-.4px;margin-bottom:6px">Nouveau mot de passe</div>
    <div style="font-size:13px;color:var(--ink3);margin-bottom:24px">Choisissez un mot de passe sécurisé.</div>

    @if($errors->any())
      <div class="alert alert-danger mb-4">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
      </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">

      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control"
               value="{{ old('email', request()->email) }}" required readonly
               style="background:var(--bg2);color:var(--ink3)">
      </div>

      <div class="form-group">
        <label class="form-label">Nouveau mot de passe</label>
        <input type="password" name="password" class="form-control"
               placeholder="Minimum 8 caractères" required autofocus>
      </div>

      <div class="form-group">
        <label class="form-label">Confirmer le mot de passe</label>
        <input type="password" name="password_confirmation" class="form-control"
               placeholder="••••••••" required>
      </div>

      <button type="submit" class="btn btn-primary w-full" style="justify-content:center;padding:10px;margin-top:8px">
        Réinitialiser le mot de passe
      </button>
    </form>
  </div>
</body>
</html>
