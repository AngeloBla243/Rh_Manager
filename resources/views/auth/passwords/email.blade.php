{{-- resources/views/auth/passwords/email.blade.php --}}
{{-- Vue de demande de réinitialisation de mot de passe --}}
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Réinitialisation — RH Manager</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body style="min-height:100vh;background:var(--ink);display:flex;align-items:center;justify-content:center;padding:24px">
  <div style="background:var(--surface);border-radius:var(--rxl);padding:40px 36px;width:400px;box-shadow:var(--sh3)">
    <div style="margin-bottom:24px">
      <a href="{{ route('login') }}" style="font-size:12px;color:var(--ink3)">← Retour à la connexion</a>
      <div style="font-size:22px;font-weight:700;letter-spacing:-.4px;margin-top:12px">Mot de passe oublié ?</div>
      <div style="font-size:13px;color:var(--ink3);margin-top:4px">
        Entrez votre email pour recevoir un lien de réinitialisation.
      </div>
    </div>

    @if(session('status'))
      <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger mb-4">{{ $errors->first('email') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
      @csrf
      <div class="form-group">
        <label class="form-label">Adresse email</label>
        <input type="email" name="email" class="form-control"
               value="{{ old('email') }}" required autofocus placeholder="admin@entreprise.com">
      </div>
      <button type="submit" class="btn btn-primary w-full" style="justify-content:center;padding:10px;margin-top:8px">
        Envoyer le lien de réinitialisation
      </button>
    </form>
  </div>
</body>
</html>


{{-- ============================================================
     resources/views/auth/passwords/reset.blade.php
     ============================================================ --}}
